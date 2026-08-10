<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletConnectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('wallet_address')) {
            $this->merge([
                'wallet_address' => strtolower((string) $this->wallet_address),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'wallet_address' => [
                'required',
                'string',
                'regex:/^0x[a-f0-9]{40}$/',
                Rule::unique('wallets', 'wallet_address')->where(function ($query) {
                    return $query->where('user_id', '!=', $this->user()->id);
                }),
            ],
        ];
    }
}
