<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reference' => 'string|uuid',
            'date' => 'date',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|in:SAR,AED,EGP,USD,EUR,GBP',
            'sender_account' => 'required|string',
            'receiver_account' => 'required|string',
            'receiver_name' => 'required|string|max:255',
            'bank_code' => 'required|string|max:50',
            'notes' => 'nullable|array',
            'notes.*' => 'string|max:255',
            'payment_type' => 'nullable|string',
            'charge_details' => 'nullable|string',
        ];
    }
}
