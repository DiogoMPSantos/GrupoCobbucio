<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'type' => fake()->randomElement(['deposit', 'transfer', 'reversal']),
            'amount' => fake()->randomFloat(2, 10, 500),
            'reference' => Str::uuid(),
            'status' => 'completed',
        ];
    }
}
