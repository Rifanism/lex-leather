<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public const STATUSES = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];

    public const PAYMENT_METHODS = ['cod', 'bank_transfer', 'ewallet', 'qris'];

    /** Methods where the customer pays up front, so an order stays unpaid until they click pay. */
    public const PREPAID_METHODS = ['bank_transfer', 'ewallet', 'qris'];

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'total_amount',
        'status',
        'payment_method',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * INV-YYYYMMDD-XXXXXX, retried on the (unlikely) unique-index collision.
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'INV-'.now()->format('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    /** COD is collected on delivery, so there is nothing for the customer to pay up front. */
    public function isPrepaid(): bool
    {
        return in_array($this->payment_method, self::PREPAID_METHODS, true);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function canBePaidByCustomer(): bool
    {
        return $this->isPending() && $this->isPrepaid();
    }

    /**
     * Single source of truth for the customer-facing label of each status.
     * Views render `$order->statusLabel()`; they never keep their own copy.
     *
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Sudah Dibayar',
            'shipped' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Single source of truth for payment method labels. `paymentMethodLabel()`
     * reads this; the checkout radio list reads it too instead of keeping its
     * own copy.
     *
     * @return array<string, string>
     */
    public static function paymentMethodLabels(): array
    {
        return [
            'cod' => 'COD (Bayar di Tempat)',
            'bank_transfer' => 'Transfer Bank',
            'ewallet' => 'E-Wallet',
            'qris' => 'QRIS',
        ];
    }

    public function paymentMethodLabel(): string
    {
        return static::paymentMethodLabels()[$this->payment_method] ?? $this->payment_method;
    }

    public function formattedTotal(): string
    {
        return 'Rp '.number_format($this->total_amount, 0, ',', '.');
    }
}
