<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Singleton row holding the account numbers shown at checkout.
 * See AGENTS.md: one slot per payment method.
 */
class PaymentSetting extends Model
{
    protected $fillable = [
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'ewallet_provider',
        'ewallet_number',
        'ewallet_holder',
        'qris_image',
    ];

    /**
     * Never returns null, so a missing seed row degrades to empty fields
     * instead of a 500 on the checkout page.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static;
    }

    /**
     * Label/value pairs the customer needs for the chosen payment method.
     *
     * @return list<array{label: string, value: string}>
     */
    public function detailsFor(string $method): array
    {
        return match ($method) {
            'bank_transfer' => $this->filledRows([
                ['label' => 'Bank', 'value' => $this->bank_name],
                ['label' => 'Nomor Rekening', 'value' => $this->bank_account_number],
                ['label' => 'Atas Nama', 'value' => $this->bank_account_holder],
            ]),
            'ewallet' => $this->filledRows([
                ['label' => 'E-Wallet', 'value' => $this->ewallet_provider],
                ['label' => 'Nomor', 'value' => $this->ewallet_number],
                ['label' => 'Atas Nama', 'value' => $this->ewallet_holder],
            ]),
            default => [],
        };
    }

    /**
     * Drop blank pairs, otherwise the checkout page renders "Bank: " with
     * nothing after it when the admin has not filled the settings in yet.
     *
     * @param  list<array{label: string, value: string|null}>  $pairs
     * @return list<array{label: string, value: string}>
     */
    private function filledRows(array $pairs): array
    {
        return array_values(array_filter($pairs, fn (array $pair) => filled($pair['value'])));
    }

    public function qrisImageUrl(): ?string
    {
        // disk('public') so the URL follows FILESYSTEM_PUBLIC_DRIVER (R2 on Vercel).
        return $this->qris_image ? Storage::disk('public')->url($this->qris_image) : null;
    }
}
