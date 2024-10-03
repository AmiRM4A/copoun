<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:percent,fixed'],
            'value' => ['required', 'integer'],
            'description' => ['sometimes', 'nullable', 'string'],
            'count' => ['sometimes', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1']
        ];
    }
}
