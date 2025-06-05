<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface WalletRepositoryInterface
{
    public function deposit(User $user, float $amount): void;
    public function transfer(User $sender, int $receiverId, float $amount): void;
    public function reverse(string $reference): void;
    public function getTransactions(User $user);
    public function getWallet(User $user);
}
