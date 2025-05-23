<?php

namespace App\Enums;

// we can extend this enum to support all ISO 4217 currencies
// but you got the idea :)
enum Currency: string
{
    case SAR = 'SAR';
    case AED = 'AED';
    case EGP = 'EGP';
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case OMR = 'OMR';

    public function label(): string
    {
        return match ($this) {
            self::SAR => 'Saudi Riyal',
            self::AED => 'UAE Dirham',
            self::EGP => 'Egyptian Pound',
            self::USD => 'US Dollar',
            self::EUR => 'Euro',
            self::GBP => 'British Pound',
            self::OMR => 'Omani Rial',
        };
    }

    public function precision(): int
    {
        return match ($this) {
            // all the listed currencies above have 2 decimal places
            self::OMR => 3, // Oman: OMR typically has 3 decimal places
            // Currencies with 2 (default)
            default => 2,
        };
    }


    public function symbol(): string
    {
        return match ($this) {
            self::SAR => '﷼',
            self::AED => 'د.إ',
            self::EGP => '£',
            self::USD => '$',
            self::EUR => '€',
            self::GBP => '£',
            self::OMR => '﷼',
        };
    }

    public static function isValid(string $code): bool
    {
        return collect(self::cases())->contains(fn($c) => $c->value === $code);
    }
}
