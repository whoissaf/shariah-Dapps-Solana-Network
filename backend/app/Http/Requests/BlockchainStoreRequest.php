<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlockchainStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proof_id' => [
                'required',
                'integer',
                Rule::exists('proofs', 'id')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id)
                        ->whereIn('status', ['generated', 'shared']);
                }),
            ],
        ];
    }
}
