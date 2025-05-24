<?php

namespace App\Http\Services\Webhook\Parsers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AcmeParser implements WebhookParserInterface
{   
    // modified with the missing data
    // SA6980000204608016212908//156,50//SAR//202506159000001//20250615
    public function parse(string $raw): array
    {
        $lines = explode("\n", trim($raw));
        $transactions = [];

        foreach ($lines as $line) {
            try {
                $line = trim($line);

                $elements = explode('//', $line);
                if (count($elements) !== 5) {
                    Log::warning("Skipping invalid ACME webhook line: {$line}");
                    continue;
                }
                [$accountId, $amount, $currency, $reference, $date] = $elements;
                // save amount as cents and use ISO 4217 to get the precision
                $amountCents = (int) str_replace(',', '', $amount);

                $transactions[] = [
                    'bank_account_id' => $accountId,
                    'amount_cents' => $amountCents,
                    'currency' => $currency,
                    'reference' => $reference,
                    'date' => Carbon::createFromFormat('Ymd', $date),
                    'meta' => [],
                ];
                Log::info("New transaction AcmeParser: {$reference}");
            } catch (\Exception $e) {
                Log::error("Error parsing line: {$line}");
            }
        }

        return $transactions;
    }
}
