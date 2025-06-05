<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    use HasFactory;

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REVERSED = 'reversed';
    public const STATUS_PENDING = 'pending';

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_REVERSAL = 'reversal';

    protected $fillable = [
        'sender_id', 'receiver_id', 'type', 'amount', 'reference', 'original_reference', 'status',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function originalTransaction()
    {
        return $this->belongsTo(Transaction::class, 'original_reference', 'reference');
    }

    public function reversal()
    {
        return $this->hasOne(Transaction::class, 'original_reference', 'reference');
    }
}
