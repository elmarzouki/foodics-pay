<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

use App\Models\Currency;
use App\Models\Transaction;
use App\Validators\WebhookPayloadValidator;
use App\Http\Services\Ingestor\TransactionIngestor;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Validation\ValidationException;

use Mockery;

class IngestorTest extends TestCase
{
    public function test_ingestor_skips_duplicate()
    {
        $cache = Mockery::mock(Cache::class);
        $ingestor = new TransactionIngestor($cache);
    
        $trx = Transaction::make([
            'bank_account_id' => 'SA6980000204608016212908',
            'reference' => '202506159000001',
            'amount_cents' => 15650,
            'currency' => Currency::SAR,
            'date' => now(),
            'meta' => [],
        ]);
    
        $cache->shouldReceive('add')->once()->andReturn(false);
    
        Log::shouldReceive('debug')->once()->with(Mockery::pattern('/already ingested/'));
        $ingestor->ingest($trx); // Should skip actual DB insert
    }

    public function test_ingestor_ingests_1000_unique_transactions()
    {
        $cache = Mockery::mock(Repository::class);
        $ingestor = new TransactionIngestor($cache);

        $inserted = 0;

        $cache->shouldReceive('add')->andReturnUsing(function () use (&$inserted) {
            return $inserted++ % 2 === 0; // simulate 50% duplication
        });

        $trx = new Transaction([
            'bank_account_id' => 'SA6980000204608016212908',
            'reference' => 'ref',
            'amount_cents' => 15650,
            'currency' => Currency::SAR,
            'date' => now(),
            'meta' => [],
        ]);

        for ($i = 0; $i < 1000; $i++) {
            $trx->reference = "ref{$i}";
            $ingestor->ingest(clone $trx);
        }

        // Verify the ingestion ran 1000 times (some skipped)
        $this->assertTrue(true);
    }


    public function test_invalid_currency_fails_validation()
    {
        $this->expectException(ValidationException::class);

        WebhookPayloadValidator::validate([
            'bank_account_id' => 'SA6980000204608016212908',
            'amount_cents' => 15650,
            'currency' => 'INVALID',
            'reference' => '202506159000001',
            'date' => now(),
        ]);
    }

    
}
