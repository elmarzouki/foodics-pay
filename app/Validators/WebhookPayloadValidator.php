<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Enums\Currency;

class WebhookPayloadValidator
{

    public static function rules(): array
    {
        return [
            'bank_account_id' => ['required', 'string', 'max:255'],
            'amount_cents' => ['required', 'integer', 'min:100'],
            'currency' => ['required', 'string', 'in:' . implode(',', array_column(Currency::cases(), 'value'))],
            'date' => ['required', 'date'],
            'reference' => ['required', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
        ];
    }
    /**
     * @throws ValidationException
     */
    public static function validate(array $data): array
    {
        $validator = Validator::make($data, self::rules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
