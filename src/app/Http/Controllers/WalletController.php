<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Http\Requests\ReverseRequest;
use App\Http\Requests\TransferRequest;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        protected WalletRepositoryInterface $walletRepository
    ) {}

    public function show(Request $request)
    {
        $wallet = $this->walletRepository->getWallet($request->user());

        return response()->json([
            'email' => $request->user()->email,
            'balance' => $wallet?->balance ?? 0,
        ]);
    }

    public function deposit(DepositRequest $request)
    {
        $this->walletRepository->deposit($request->user(), $request->amount);

        return response()->json(['message' => 'Deposit successful']);
    }

    public function transfer(TransferRequest $request)
    {
        $user = $request->user();
        $receiverId = $request->receiver_id;

        if ($receiverId == $user->id) {
            return response()->json(['error' => 'Cannot transfer to yourself'], 422);
        }

        try {
            $this->walletRepository->transfer($user, $receiverId, $request->amount);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Transfer successful']);
    }

    public function reverseTransaction(ReverseRequest $request)
    {
        try {
            $this->walletRepository->reverse($request->reference);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Transaction reversed successfully']);
    }

    public function transactions(Request $request)
    {
        $user = $request->user();
        $transactions = $this->walletRepository->getTransactions($user);

        $data = $transactions->map(function ($tx) use ($user) {
            return [
                'id' => $tx->id,
                'reference' => $tx->reference,
                'type' => $tx->type,
                'amount' => $tx->amount,
                'status' => $tx->status,
                'date' => $tx->created_at->toDateTimeString(),
                'direction' => $tx->sender_id === $user->id ? 'sent' : 'received',
                'counterparty' => $tx->sender_id === $user->id ? $tx->receiver?->email : $tx->sender?->email,
            ];
        });

        return response()->json([
            'data' => $data,
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
            'per_page' => $transactions->perPage(),
            'total' => $transactions->total(),
        ]);
    }
}
