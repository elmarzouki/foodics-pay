<?php

namespace Tests\Unit;

use Tests\TestCase;
use Faker\Factory as Faker;

use App\Http\Services\Webhook\Parsers\FoodicsParser;
use Tests\Factories\WebhookPayloadFactory;

class FoodicsParserTest extends TestCase
{
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
    }

    public function test_parses_valid_foodics_line()
    {
        $accountId = 'SA' . $this->faker->numerify('###000000###########');
        $date = now()->format('Ymd');
        $amount = number_format($this->faker->randomFloat(2, 100, 9999), 2, ',', '');
        $currency = 'SAR';
        $reference = $date . $this->faker->numerify('######');
        $meta = 'note/debt payment march/internal_reference/A462JE81';

        $line = "{$accountId}#{$date}{$amount}#{$currency}#{$reference}#{$meta}";

        $parser = new FoodicsParser();
        $results = $parser->parse($line);

        $this->assertCount(1, $results);
        $trx = $results[0];

        $amountCents = (int) str_replace(',', '', $amount);
        $this->assertEquals($accountId, $trx['bank_account_id']);
        $this->assertEquals($amountCents, $trx['amount_cents']);
        $this->assertEquals($currency, $trx['currency']);
        $this->assertEquals($reference, $trx['reference']);
        $this->assertEquals('debt payment march', $trx['meta']['note']);
    }

    public function test_parsing_1000_transactions_is_fast_enough()
    {
        $parser = new FoodicsParser();
        $payload = WebhookPayloadFactory::foodics(1000);

        $start = microtime(true);
        $transactions = $parser->parse($payload);
        $end = microtime(true);

        $durationMs = ($end - $start) * 1000;

        $this->assertCount(1000, $transactions);
        foreach ($transactions as $trx) {
            $this->assertNotNull($trx['amount_cents']);
            $this->assertNotNull($trx['reference']);
        }
        $this->assertLessThan(10000, $durationMs, "Parser took too long: {$durationMs} ms");
    }

    public function test_parser_ignores_invalid_lines_gracefully()
    {
        $parser = new FoodicsParser();

        $validLine1 = WebhookPayloadFactory::foodics(1);
        $validLine2 = WebhookPayloadFactory::foodics(1);

        $payload = implode("\n", [
            trim($validLine1),
            "INVALID#LINE#FORMAT",
            trim($validLine2),
        ]);

        $transactions = $parser->parse($payload);

        $this->assertCount(2, $transactions);
    }
}
