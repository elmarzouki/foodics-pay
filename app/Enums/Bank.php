<?php

namespace App\Enums;

use App\Http\Services\Webhook\Parsers\FoodicsParser;
use App\Http\Services\Webhook\Parsers\AcmeParser;
use App\Http\Services\Webhook\Parsers\WebhookParserInterface;

enum Bank: string
{
    case FOODICS = 'foodics';
    case ACME = 'acme';

    public function parser(): WebhookParserInterface
    {
        return match($this) {
            self::FOODICS => app(FoodicsParser::class),
            self::ACME => app(AcmeParser::class),
        };
    }

    public static function fromString(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if (strtolower($case->value) === strtolower($name)) {
                return $case;
            }
        }
        return null;
    }
}