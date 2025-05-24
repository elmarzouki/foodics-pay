<?php

namespace Tests\Unit;

use Tests\TestCase;
use Faker\Factory as Faker;
use App\Http\Services\Webhook\Parsers\AcmeParser;
use Tests\Factories\WebhookPayloadFactory;

class AcmeParserTest extends TestCase
{
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
    }

    public function test_parses_valid_acme_line()
    {
        $accountId = 'SA' . $this->faker->numerify('###000000###########');
        $amount = number_format($this->faker->randomFloat(2, 100, 9999), 2, ',', '');
        $currency = 'SAR';
        $date = now()->format('Ymd');
        $reference = $date . $this->faker->numerify('######');

        $line = "{$accountId}//{$amount}//{$currency}//{$reference}//{$date}";
        $parser = new AcmeParser();
        $results = $parser->parse($line);

        $this->assertCount(1, $results);
        $trx = $results[0];

        $amountCents = (int) str_replace(',', '', $amount);
        $this->assertEquals($accountId, $trx['bank_account_id']);
        $this->assertEquals($amountCents, $trx['amount_cents']);
        $this->assertEquals($currency, $trx['currency']);
        $this->assertEquals($reference, $trx['reference']);
    }

    public function test_parsing_1000_transactions_is_fast_enough()
    {
        $payload = WebhookPayloadFactory::acme(1000);
        $parser = new AcmeParser();

        $start = microtime(true);
        $transactions = $parser->parse($payload);
        $end = microtime(true);

        $durationMs = ($end - $start) * 1000;

        $this->assertCount(1000, $transactions);
        foreach ($transactions as $trx) {
            $this->assertNotNull($trx['amount_cents']);
            $this->assertNotNull($trx['reference']);
        }
        $this->assertLessThan(3000, $durationMs, "Parser took too long: {$durationMs} ms");
    }
    
    public function test_parser_ignores_invalid_lines_gracefully()
    {
        $validLine1 = WebhookPayloadFactory::acme(1);
        $validLine2 = WebhookPayloadFactory::acme(1);


        $payload = implode("\n", [
            trim($validLine1),
            'INVALID//LINE',
            trim($validLine2),
        ]);


        $parser = new AcmeParser();
        $transactions = $parser->parse($payload);

        $this->assertCount(2, $transactions);
    }

}
