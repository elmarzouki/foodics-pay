<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Services\Webhook\Parsers\AcmeParser;
use Tests\WebhookPayloadGenerator;

class AcmeParserTest extends TestCase
{
    public function test_parses_valid_acme_line()
    {
        $line = 'SA6980000204608016212908//156,50//SAR//202506159000001//20250615';
        $parser = new AcmeParser();

        $results = $parser->parse($line);

        $this->assertCount(1, $results);
        $trx = $results[0];

        $this->assertEquals('SA6980000204608016212908', $trx['bank_account_id']);
        $this->assertEquals(15650, $trx['amount_cents']);
        $this->assertEquals('SAR', $trx['currency']);
        $this->assertEquals('202506159000001', $trx['reference']);
    }

    public function test_parsing_1000_transactions_is_fast_enough()
    {
        $payload = WebhookPayloadGenerator::acme(1000);
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
        $parser = new AcmeParser();

        $payload = <<<EOT
            SA6980000204608016212908//156,50//SAR//202506159000001//20250615
            INVALID//LINE
            SA6980000204608016212908//178,25//SAR//202506159000002//20250615
            EOT;

        $transactions = $parser->parse($payload);

        $this->assertCount(2, $transactions);
    }

}
