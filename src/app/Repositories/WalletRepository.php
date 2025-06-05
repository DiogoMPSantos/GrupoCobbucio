<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletRepository implements WalletRepositoryInterface
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

    public function transfer(User $sender, int $receiverId, float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Transfer amount must be positive.");
        }

        $receiver = User::findOrFail($receiverId);

        DB::transaction(function () use ($sender, $receiver, $amount) {
            $senderWallet = $sender->wallet;
            $receiverWallet = $receiver->wallet ?? Wallet::create([
                'user_id' => $receiver->id,
                'balance' => 0,
            ]);

            if (!$senderWallet || $senderWallet->balance < $amount) {
                throw new \RuntimeException("Insufficient funds.");
            }

            $senderWallet->balance -= $amount;
            $receiverWallet->balance += $amount;

            $senderWallet->save();
            $receiverWallet->save();

            Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'type' => Transaction::TYPE_TRANSFER,
                'amount' => $amount,
                'status' => Transaction::STATUS_COMPLETED,
                'reference' => Str::uuid(),
            ]);
        });
    }

    public function reverse(string $reference): void
    {
        
        $original = Transaction::where('reference', $reference)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->firstOrFail();
    
        DB::transaction(function () use ($original) {
    
            if ($original->type === Transaction::TYPE_DEPOSIT) {
                $receiver = User::findOrFail($original->receiver_id);
                $receiverWallet = $receiver->wallet;
    
                if (!$receiverWallet || $receiverWallet->balance < $original->amount) {
                    throw new \RuntimeException("Insufficient funds to reverse deposit.");
                }
    
                $receiverWallet->balance -= $original->amount;
                $receiverWallet->save();
    
                $original->update(['status' => Transaction::STATUS_REVERSED]);
    
                Transaction::create([
                    'receiver_id' => $receiver->id,
                    'type' => Transaction::TYPE_REVERSAL,
                    'amount' => $original->amount,
                    'status' => Transaction::STATUS_COMPLETED,
                    'reference' => Str::uuid(),
                    'original_reference' => $original->reference,
                ]);
            } elseif ($original->type === Transaction::TYPE_TRANSFER) {
                $sender = User::findOrFail($original->sender_id);
                $receiver = User::findOrFail($original->receiver_id);
    
                $senderWallet = $sender->wallet;
                $receiverWallet = $receiver->wallet;
    
                if (!$receiverWallet || $receiverWallet->balance < $original->amount) {
                    throw new \RuntimeException("Insufficient funds to reverse transfer.");
                }
    
                $receiverWallet->balance -= $original->amount;
                $senderWallet->balance += $original->amount;
    
                $receiverWallet->save();
                $senderWallet->save();
    
                $original->update(['status' => Transaction::STATUS_REVERSED]);
    
                Transaction::create([
                    'sender_id' => $receiver->id,
                    'receiver_id' => $sender->id,
                    'type' => Transaction::TYPE_REVERSAL,
                    'amount' => $original->amount,
                    'status' => Transaction::STATUS_COMPLETED,
                    'reference' => Str::uuid(),
                    'original_reference' => $original->reference,
                ]);
            } else {
                throw new \RuntimeException("Unsupported transaction type for reversal.");
            }
        });
    }

    public function getTransactions(User $user)
    {
        return Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->latest()
            ->paginate();
    }

    public function getWallet(User $user)
    {
        return $user->wallet;
    }
}
