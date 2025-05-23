<?php

namespace Tests;

class WebhookPayloadGenerator
{
    public static function foodics(int $count): string
    {
        $lines = [];

        for ($i = 0; $i < $count; $i++) {
            $date = now()->format('Ymd');
            $amount = number_format(rand(100, 9999) + rand(0, 99) / 100, 2, ',', '');
            $reference = "{$date}{$i}";

            $lines[] = "SA6980000204608016212908#{$date}{$amount}#SAR#{$reference}#note/payment{$i}/internal_reference/XYZ{$i}";
        }

        return implode("\n", $lines);
    }

    public static function acme(int $count): string
    {
        $lines = [];

        for ($i = 0; $i < $count; $i++) {
            $amount = number_format(rand(100, 9999) + rand(0, 99) / 100, 2, ',', '');
            $date = now()->format('Ymd');
            $reference = "{$date}{$i}";

            $lines[] = "SA6980000204608016212908//{$amount}//SAR//{$reference}//{$date}";
        }

        return implode("\n", $lines);
    }
}