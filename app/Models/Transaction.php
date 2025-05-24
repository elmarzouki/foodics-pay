<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Validators\WebhookPayloadValidator;

use App\Enums\Currency;

class Transaction extends Model
{
    protected $fillable = [
        'bank_account_id',
        'amount_cents',
        'currency',
        'date',
        'reference',
        'meta',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'meta' => 'array',
        'date' => 'datetime',
        'currency' => Currency::class,
    ];

    // this is a factory method to create a new transaction (Eloquent model) from webhook data
    public static function fromWebhook(array $data): self
    {
        $validated = WebhookPayloadValidator::validate($data);

        return new self([
            'bank_account_id' => $validated['bank_account_id'],
            'amount_cents' => $validated['amount_cents'],
            'currency' => $validated['currency'],
            'date' => $validated['date'],
            'reference' => $validated['reference'],
            'meta' => $validated['meta'] ?? [],
        ]);
    }
}
