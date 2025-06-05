<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function deposit(User $user, float $amount): void
    {

        if ($amount <= 0) {
            throw new \InvalidArgumentException("Deposit amount must be positive.");
        }
        
        DB::transaction(function () use ($user, $amount) {
            $wallet = $user->wallet ?? Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);

            $wallet->balance += $amount;
            $wallet->save();

            Transaction::create([
                'sender_id' => null,
                'receiver_id' => $user->id,
                'type' => 'deposit',
                'amount' => $amount,
                'status' => Transaction::STATUS_COMPLETED,
                'reference' => Str::uuid(),
            ]);
        });
    }

    public function transfer(User $sender, int $receiverId, float $amount): bool
    {
        return DB::transaction(function () use ($sender, $receiverId, $amount) {
            $senderWallet = $sender->wallet;
            if (!$senderWallet || $senderWallet->balance < $amount) {
                return false;
            }

            $receiver = User::findOrFail($receiverId);
            $receiverWallet = $receiver->wallet ?? Wallet::create([
                'user_id' => $receiverId,
                'balance' => 0,
            ]);

            $senderWallet->balance -= $amount;
            $senderWallet->save();

            $receiverWallet->balance += $amount;
            $receiverWallet->save();

            Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiverId,
                'type' => 'transfer',
                'amount' => $amount,
                'status' => Transaction::STATUS_COMPLETED,
                'reference' => Str::uuid(),
            ]);

            return true;
        });
    }

    public function reverseTransaction(User $user, string $reference): bool|string
    {
        return DB::transaction(function () use ($user, $reference) {
            $original = Transaction::where('reference', $reference)->first();
        
            if (
                $user->id !== $original->sender_id &&
                $user->id !== $original->receiver_id
            ) {
                return 'You are not authorized to reverse this transaction.';
            }
        
            if (!$original || $original->type === 'reversal') {
                return 'Invalid transaction for reversal.';
            }
        
            $alreadyReversed = Transaction::where('type', 'reversal')
                ->where('reference', $reference)
                ->exists();
        
            if ($alreadyReversed) {
                return 'Transaction already reversed.';
            }
        
            $senderWallet = $original->sender_id ? Wallet::where('user_id', $original->sender_id)->first() : null;
            $receiverWallet = $original->receiver_id ? Wallet::where('user_id', $original->receiver_id)->first() : null;
        
            if ($original->type === 'deposit') {
                if (!$receiverWallet || $receiverWallet->balance < $original->amount) {
                    return 'Insufficient balance to reverse deposit.';
                }
        
                $receiverWallet->balance -= $original->amount;
                $receiverWallet->save();
            }
        
            if ($original->type === 'transfer') {
                if (!$receiverWallet || $receiverWallet->balance < $original->amount) {
                    return 'Receiver has insufficient balance to reverse transfer.';
                }
        
                $receiverWallet->balance -= $original->amount;
                $receiverWallet->save();
        
                if (!$senderWallet) {
                    $senderWallet = Wallet::create([
                        'user_id' => $original->sender_id,
                        'balance' => 0,
                    ]);
                }
        
                $senderWallet->balance += $original->amount;
                $senderWallet->save();
            }
        
            Transaction::create([
                'sender_id' => $original->receiver_id,
                'receiver_id' => $original->sender_id,
                'type' => 'reversal',
                'amount' => $original->amount,
                'status' => Transaction::STATUS_REVERSED,
                'reference' => (string) \Illuminate\Support\Str::uuid(),
                'original_reference' => $reference,
            ]);
        
            return true;
        });
        
    }
}
