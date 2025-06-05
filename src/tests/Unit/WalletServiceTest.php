<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletService = new WalletService();
    }

    public function test_deposit_adds_amount_to_balance()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100,
        ]);
        
        $this->walletService->deposit($user, 50);

        $updatedWallet = $wallet->fresh();
        $this->assertEquals(150, $updatedWallet->balance);
    }

    public function test_deposit_negative_amount_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = User::factory()->create();
        $this->walletService->deposit($user, -20);
    }

    public function test_transfer_with_sufficient_balance()
    {
        $fromUser = User::factory()->create();
        $toUser = User::factory()->create();

        $fromWallet = Wallet::factory()->create([
            'user_id' => $fromUser->id,
            'balance' => 100,
        ]);
        $toWallet = Wallet::factory()->create([
            'user_id' => $toUser->id,
            'balance' => 0,
        ]);

        $this->walletService->transfer($fromUser, $toUser->id, 60);

        $this->assertEquals(40, $fromWallet->fresh()->balance);
        $this->assertEquals(60, $toWallet->fresh()->balance);
    }

    public function test_transfer_with_insufficient_balance_throws_exception()
    {
        $this->expectException(\Exception::class);

        $from = User::factory()->create(['balance' => 10]);
        $to = User::factory()->create();

        $this->walletService->transfer($from, $to->id, 100);
    }

    public function test_reverse_transfer_transaction_restores_balances()
    {
        $fromUser = User::factory()->create();
        $toUser = User::factory()->create();
    
        $fromWallet = Wallet::factory()->create([
            'user_id' => $fromUser->id,
            'balance' => 70,
        ]);
        $toWallet = Wallet::factory()->create([
            'user_id' => $toUser->id,
            'balance' => 80,
        ]);
    
        $transferReference = (string) Str::uuid();
        $depositReference = (string) Str::uuid();
    
        Transaction::create([
            'sender_id' => $fromUser->id,
            'receiver_id' => $toUser->id,
            'type' => 'transfer',
            'amount' => 30,
            'reference' => $transferReference,
            'status' => 'completed',
        ]);
    
        Transaction::create([
            'sender_id' => null,
            'receiver_id' => $toUser->id,
            'type' => 'deposit',
            'amount' => 30,
            'reference' => $depositReference,
            'status' => 'completed',
        ]);


        $this->assertEquals(70, $fromWallet->balance);
        $this->assertEquals(80, $toWallet->balance);

        $this->walletService->reverseTransaction($fromUser, $transferReference);

        $this->assertEquals(100, Wallet::where('user_id', $fromUser->id)->first()->balance);
        $this->assertEquals(50, Wallet::where('user_id', $toUser->id)->first()->balance);

        $this->assertDatabaseHas('transactions', [
            'type' => 'reversal',
            'original_reference' => $transferReference,
            'status' => Transaction::STATUS_REVERSED,
        ]);
    }

    public function test_reverse_deposit_transaction_restores_balance()
    {
        $user = User::factory()->create();

        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 80,
        ]);

        $depositReference = (string) Str::uuid();

        Transaction::create([
            'sender_id' => null,
            'receiver_id' => $user->id,
            'type' => 'deposit',
            'amount' => 30,
            'reference' => $depositReference,
            'status' => 'completed',
        ]);

        $this->assertEquals(80, $wallet->balance);

        $this->walletService->reverseTransaction($user, $depositReference);

        $wallet->refresh();

        $this->assertEquals(50, $wallet->balance);

        $this->assertDatabaseHas('transactions', [
            'type' => 'reversal',
            'original_reference' => $depositReference,
            'status' => Transaction::STATUS_REVERSED,
        ]);
    }

    public function test_reverse_transaction_fails_when_reference_invalid()
    {
        $this->expectException(\Exception::class);

        $user = User::factory()->create();
        $this->walletService->reverseTransaction($user, 'invalid-ref');
    }
}
