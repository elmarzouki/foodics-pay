<?php

namespace Tests\Unit;

use App\Http\Services\Transaction\TransactionDTO;
use App\Enums\Currency;
use App\Http\Services\Transaction\TransactionIngestor;
use App\Models\Transaction;
use App\Validators\WebhookPayloadValidator;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Tests\Factories\TransactionDTOFactory;

class IngestorTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    

    public function test_it_skips_duplicate_transaction()
    {
        $cache = Mockery::mock(Repository::class);
        $cache->shouldReceive('add')->once()->andReturn(false); // cache key already exists

        $dto = TransactionDTOFactory::makeTransactionDTO();

        Log::shouldReceive('debug')->once()->with(Mockery::pattern('/already ingested/'));

        $ingestor = new TransactionIngestor($cache);
        $ingestor->ingest($dto);

        $this->assertDatabaseMissing('transactions', [
            'reference' => $dto->reference,
            'bank_account_id' => $dto->bankAccountId,
        ]);
    }

    public function test_it_ingests_1000_transactions_with_50_percent_skipped()
    {
        $cache = Mockery::mock(Repository::class);

        $inserted = 0;

        $cache->shouldReceive('add')->andReturnUsing(function () use (&$inserted) {
            return $inserted++ % 2 === 0; // Simulate 50% deduplication
        });

        $ingestor = new TransactionIngestor($cache);

        for ($i = 0; $i < 1000; $i++) {
            $dto = TransactionDTOFactory::makeTransactionDTO([
                'reference' => "ref-$i",
            ]);
            $ingestor->ingest($dto);
        }

        $this->assertEquals(500, Transaction::count());
    }

    public function test_it_fails_validation_for_invalid_currency()
    {
        $this->expectException(ValidationException::class);

        WebhookPayloadValidator::validate([
            'bank_account_id' => 'SA6980000204608016212908',
            'amount_cents' => 15650,
            'currency' => 'INVALID', // not in enum
            'reference' => '202506159000001',
            'date' => now(),
        ]);
    }
}
