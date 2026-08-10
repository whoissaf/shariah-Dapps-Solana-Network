<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerificationReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proof_id' => ['required', 'integer', Rule::exists('proofs', 'id')],
            'nonce' => ['required', 'string', 'size:32'],
            'signature' => ['required', 'string', 'size:64'],
            'expires_at' => ['required', 'date'],
        ];
    }
}
