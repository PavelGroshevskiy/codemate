<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Transaction types.
     */
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAW = 'withdraw';
    const TYPE_TRANSFER_IN = 'transfer_in';
    const TYPE_TRANSFER_OUT = 'transfer_out';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'to_user_id',
        'from_user_id',
        'type',
        'amount',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the related user for transfer transactions.
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Scope a query to only include deposits.
     */
    public function scopeDeposits($query)
    {
        return $query->where('type', self::TYPE_DEPOSIT);
    }

    /**
     * Scope a query to only include withdrawals.
     */
    public function scopeWithdrawals($query)
    {
        return $query->where('type', self::TYPE_WITHDRAW);
    }

    /**
     * Scope a query to only include transfers.
     */
    public function scopeTransfers($query)
    {
        return $query->whereIn('type', [self::TYPE_TRANSFER_IN, self::TYPE_TRANSFER_OUT]);
    }

    /**
     * Check if transaction is a deposit.
     */
    public function isDeposit(): bool
    {
        return $this->type === self::TYPE_DEPOSIT;
    }

    /**
     * Check if transaction is a withdrawal.
     */
    public function isWithdrawal(): bool
    {
        return $this->type === self::TYPE_WITHDRAW;
    }

    /**
     * Check if transaction is a transfer.
     */
    public function isTransfer(): bool
    {
        return in_array($this->type, [self::TYPE_TRANSFER_IN, self::TYPE_TRANSFER_OUT]);
    }

//    /**
//     * Get formatted amount with sign.
//     */
//    protected function formattedAmount(): Attribute
//    {
//        return Attribute::make(
//            get: function () {
//                $sign = in_array($this->type, [self::TYPE_DEPOSIT, self::TYPE_TRANSFER_IN]) ? '+' : '-';
//                return $sign . number_format($this->amount, 2, '.', ' ');
//            }
//        );
//    }

    /**
     * Get transaction type in human readable format.
     */
    protected function typeName(): Attribute
    {
        $types = [
            self::TYPE_DEPOSIT => 'Пополнение',
            self::TYPE_WITHDRAW => 'Списание',
            self::TYPE_TRANSFER_IN => 'Входящий перевод',
            self::TYPE_TRANSFER_OUT => 'Исходящий перевод',
        ];

        return Attribute::make(
            get: fn () => $types[$this->type] ?? $this->type
        );
    }
}
