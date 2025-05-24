<?php

namespace App\Http\Services\Transaction;

use App\Enums\Currency;
use Carbon\Carbon;

class TransactionDTO
{
    public function __construct(
        public string $reference,
        public string $bankAccountId,
        public int $amountCents,
        public Currency $currency,
        public Carbon $date,
        public array $meta = [],
    ) {}

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'bank_account_id' => $this->bankAccountId,
            'amount_cents' => $this->amountCents,
            'currency' => $this->currency,
            'date' => $this->date,
            'meta' => $this->meta,
        ];
    }

    public function cacheKey(): string
    {
        return "transactions:dedup:{$this->bankAccountId}:{$this->reference}";
    }
}
