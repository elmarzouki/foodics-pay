<?php

namespace Tests\Factories;
use Faker\Factory as Faker;

class WebhookPayloadFactory
{
    public static function foodics(int $count = 1): string
    {
        $faker = Faker::create();
        $lines = [];

        for ($i = 0; $i < $count; $i++) {
            $date = now()->format('Ymd');
            $amount = number_format($faker->randomFloat(2, 100, 9999), 2, ',', '');
            $reference = "{$date}{$i}";
            $note = str_replace(['#', '/'], '', $faker->words(2, true));
            $internalRef = strtoupper($faker->bothify('???###'));

            $lines[] = "SA{$faker->numerify('###000000###########')}#{$date}{$amount}#SAR#{$reference}#note/{$note}/internal_reference/{$internalRef}";
        }

        return implode("\n", $lines);
    }

    public static function acme(int $count = 1): string
    {
        $faker = Faker::create();
        $lines = [];

        for ($i = 0; $i < $count; $i++) {
            $date = now()->format('Ymd');
            $amount = number_format($faker->randomFloat(2, 100, 9999), 2, ',', '');
            $reference = "{$date}{$i}";

            $lines[] = "SA{$faker->numerify('###000000###########')}//{$amount}//SAR//{$reference}//{$date}";
        }

        return implode("\n", $lines);
    }
}