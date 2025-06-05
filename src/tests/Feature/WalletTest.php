<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        $this->token = $loginResponse['access_token'];
    }

    public function test_deposit()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson('/api/deposit', [
                             'amount' => 100,
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Deposit successful']);
        $this->assertDatabaseHas('transactions', [
            'receiver_id' => $this->user->id,
            'type' => 'deposit',
            'amount' => 100,
            'status' => 'completed',
        ]);
    }

    public function test_transfer()
    {
        $receiver = User::factory()->create();

        // Cria ou atualiza o saldo da carteira do remetente
        $this->user->wallet()->updateOrCreate(
            ['user_id' => $this->user->id],
            ['balance' => 200]
        );

        // Cria carteira do receptor
        $receiver->wallet()->create(['balance' => 0]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                    ->postJson('/api/transfer', [
                        'receiver_id' => $receiver->id,
                        'amount' => 150,
                    ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Transfer successful']);

        // Corrige o valor de amount para string com duas casas decimais
        $this->assertDatabaseHas('transactions', [
            'sender_id' => $this->user->id,
            'receiver_id' => $receiver->id,
            'type' => 'transfer',
            'amount' => 150,
            'status' => 'completed',
        ]);
    }

    public function test_reverse_deposit_transaction()
    {
         // Cria o usuário autenticado com carteira
        $user = $this->user;
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 200,
        ]);

        $uuid = (string) Str::uuid();

        // Cria uma transação de depósito que será revertida
        $transaction = Transaction::factory()->create([
            'receiver_id' => $user->id,
            'type' => 'deposit',
            'amount' => 100,
            'reference' => $uuid,
            'status' => 'completed',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/reverse', [
                            'reference' => $uuid,
                        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Transaction reversed successfully']);

        $this->assertEquals(100, $wallet->fresh()->balance);

        $this->assertDatabaseHas('transactions', [
            'type' => 'reversal',
            'original_reference' => $uuid,
            'status' => 'completed',
        ]);
    }

    public function test_reverse_transfer_transaction()
    {
        $sender = $this->user;
        $receiver = User::factory()->create();

        // Cria as carteiras com saldos após a transferência
        $senderWallet = Wallet::factory()->create([
            'user_id' => $sender->id,
            'balance' => 70, // após transferência
        ]);

        $receiverWallet = Wallet::factory()->create([
            'user_id' => $receiver->id,
            'balance' => 80, // após receber transferência
        ]);

        $uuid = (string) Str::uuid();

        // Cria transação de transferência a ser revertida
        $transaction = Transaction::factory()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'type' => 'transfer',
            'amount' => 30,
            'reference' => $uuid,
            'status' => 'completed',
        ]);

        // Envia requisição de reversão
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/reverse', [
                            'reference' => $uuid,
                        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Transaction reversed successfully']);

        // Verifica os saldos atualizados
        $this->assertEquals(100, $senderWallet->fresh()->balance);   // 70 + 30
        $this->assertEquals(50, $receiverWallet->fresh()->balance);  // 80 - 30

        // Verifica se a transação de reversão foi registrada
        $this->assertDatabaseHas('transactions', [
            'type' => 'reversal',
            'original_reference' => $uuid,
            'status' => 'completed',
        ]);
    }

}
