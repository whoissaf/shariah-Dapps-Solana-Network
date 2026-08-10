<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RuleValidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'claim_id' => [
                'required',
                'integer',
                Rule::exists('claims', 'id')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                }),
            ],
        ];
    }
}
