<?php

namespace App\Http\Services\Transaction;

use App\Models\Transaction;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Log;

// this is the class that will ingest the transactions
// as the ingestion logic will be the same for all banks
// else we would apply the strategy pattern
class TransactionIngestor
{
    public function __construct(private Cache $cache) {}

    public function ingest(Transaction $trx): void
    {
        $cacheKey = "transactions:dedup:{$trx->bank_account_id}:{$trx->reference}";

        // Try to set if not exists using atomic cache store (Redis supports it)
        $wasSet = $this->cache->add($cacheKey, true, now()->addDay());


        if (!$wasSet) {
            Log::debug("Transaction already ingested, skipping: {$trx->reference} for bank account: {$trx->bank_account_id}");
            // return;
        }

        try {
            Log::debug('Transaction payload', $trx->attributesToArray());
            Transaction::create($trx->attributesToArray());
        } catch (Exception $e) {
            Log::error("Error ingesting transaction: {$e->getMessage()}");
            return;
        }

        Log::debug("Transaction ingested: {$trx->reference} for bank account: {$trx->bank_account_id}");
    }
}