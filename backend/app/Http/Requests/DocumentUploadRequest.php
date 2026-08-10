<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentUploadRequest extends FormRequest
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
            'document_type' => [
                'required',
                Rule::in([
                    'salary',
                    'business_certificate',
                    'license',
                    'other',
                ]),
            ],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
