<?php

namespace App\Http\Services\Webhook\Parsers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class FoodicsParser implements WebhookParserInterface
{
    private function parseMeta(string $metaRaw): array
    {
        $metaPairs = explode('/', $metaRaw);
        $meta = [];
        for ($i = 0; $i < count($metaPairs); $i += 2) {
            $key = $metaPairs[$i];  
            $value = $metaPairs[$i + 1] ?? null; // In case the string has odd elements
            $meta[$key] = $value;
        }
        return $meta;
    }

    // modified with the missing data
    // SA6980000204608016212908#20250615156,50#SAR#202506159000001#note/debt payment march/internal_reference/A462JE81
    private function parseLine(string $line): ?array
    {
        $elements = explode('#', $line);
        if (count($elements) !== 5) {
            Log::warning("Skipping invalid Foodics webhook line: {$line}");
            return null;
        }
        [$accountId, $dateAmount, $currency, $reference, $metaRaw] = $elements;
        $date = substr($dateAmount, 0, 8); // date is the first 8 characters
        $amount = substr($dateAmount, 8); // amount is the last 6 characters
        // save amount as cents and use ISO 4217 to get the precision
        $amountCents = (int) str_replace(',', '', $amount);

        $meta = $this->parseMeta($metaRaw);
        return [
            'bank_account_id' => $accountId,
            'date' => Carbon::createFromFormat('Ymd', $date),
            'amount_cents' => $amountCents,
            'currency' => $currency,
            'reference' => $reference,
            'meta' => $meta,
        ];
    }

    public function parse(string $raw): array
    {
        $lines = explode("\n", trim($raw));
        $transactions = [];

        foreach ($lines as $line) {
            try {
                $line = trim($line);

                $transaction = $this->parseLine($line);

                if ($transaction) { // if the transaction is valid
                    $transactions[] = $transaction;
                    Log::info("New transaction FoodicsParser: {$transaction['reference']}");
                }

            } catch (Exception $e) {
                Log::error("Error parsing line: {$line}");
            }
        }

        return $transactions;
    }
}
