<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Every field is required on purpose: an empty submit must come back as a
     * 422 with messages, never as a database error.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(Order::PAYMENT_METHODS)],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name' => 'nama penerima',
            'customer_email' => 'email',
            'customer_phone' => 'nomor telepon',
            'shipping_address' => 'alamat pengiriman',
            'payment_method' => 'metode pembayaran',
        ];
    }
}
