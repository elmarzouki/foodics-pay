<?php

namespace App\Http\Services\Transaction;

use App\Models\Transaction;
use App\Http\Services\Transaction\TransactionDTO;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Log;
use Exception;
// this is the class that will ingest the transactions
// as the ingestion logic will be the same for all banks
// else we would apply the strategy pattern
class TransactionIngestor
{
    public function __construct(private Cache $cache) {}

    public function ingest(TransactionDTO $dto): void
    {
        $cacheKey = $dto->cacheKey();

        // Try to set if not exists using atomic cache store (Redis supports it)
        $wasSet = $this->cache->add($cacheKey, true, now()->addDay());


        if (!$wasSet) {
            Log::debug("Transaction already ingested, skipping: {$dto->reference} for bank account: {$dto->bankAccountId}");
            return;
        }

        try {
            Log::debug('Transaction payload', $dto->toArray());
            Transaction::create($dto->toArray());
        } catch (Exception $e) {
            Log::error("Error ingesting transaction: {$e->getMessage()}");
            return;
        }

        Log::debug("Transaction ingested: {$dto->reference} for bank account: {$dto->bankAccountId}");
    }
}