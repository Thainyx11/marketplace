<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAcheteur();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Kept short on purpose: it round-trips through Stripe Checkout metadata (500 char cap).
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_method' => ['required', 'in:standard,express'],
            'promo_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
