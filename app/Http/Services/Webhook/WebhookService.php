<?php

namespace App\Http\Services\Webhook;

use App\Models\Transaction;
use App\Http\Services\Transaction\TransactionIngestor;
use App\Http\Services\Webhook\Parsers\WebhookParserInterface;

class WebhookService
{
    public function __construct(private TransactionIngestor $ingestor) {}

    public function process(string $body, WebhookParserInterface $parser): void
    {
        $transactions = $parser->parse($body);
        
        foreach ($transactions as $trxData) { 
            $trx = Transaction::fromWebhook($trxData);  
            $this->ingestor->ingest($trx);
        }
    }
}
