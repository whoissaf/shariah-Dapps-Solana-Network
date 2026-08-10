<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClaimCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'claim_type' => [
                'required',
                Rule::in([
                    'income_threshold',
                    'age_minimum',
                    'business_category_halal',
                    'no_active_restricted_financing',
                ]),
                Rule::unique('claims', 'claim_type')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id)
                        ->whereIn('status', [
                            'draft',
                            'submitted',
                            'eligible',
                            'proof_generated',
                        ]);
                }),
            ],
            'payload' => ['required', 'array'],
        ];

        switch ($this->input('claim_type')) {
            case 'income_threshold':
                $rules['payload.monthly_income'] = ['required', 'numeric', 'min:0'];
                break;
            case 'age_minimum':
                $rules['payload.date_of_birth'] = ['required', 'date', 'before_or_equal:today'];
                break;
            case 'business_category_halal':
                $rules['payload.business_category'] = ['required', 'string', 'max:255'];
                break;
            case 'no_active_restricted_financing':
                $rules['payload.has_restricted_financing'] = ['required', 'boolean'];
                break;
        }

        return $rules;
    }
}
