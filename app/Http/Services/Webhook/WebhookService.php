<?php

namespace App\Http\Services\Webhook;

use App\Http\Services\Transaction\TransactionDTO;
use App\Http\Services\Transaction\TransactionIngestor;
use App\Http\Services\Webhook\Parsers\WebhookParserInterface;
use App\Enums\Currency;
use Carbon\Carbon;

class WebhookService
{
    public function __construct(private TransactionIngestor $ingestor) {}

    public function process(string $body, WebhookParserInterface $parser): void
    {
        $transactions = $parser->parse($body);
        
        foreach ($transactions as $trxData) { 
            $dto = new TransactionDTO(
                reference: $trxData['reference'],
                bankAccountId: $trxData['bank_account_id'],
                amountCents: $trxData['amount_cents'],
                currency: Currency::from($trxData['currency']),
                date: Carbon::parse($trxData['date']),
                meta: $trxData['meta'] ?? [],
            );
            $this->ingestor->ingest($dto);
        }
    }
}
