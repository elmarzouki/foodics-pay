<?php

namespace App\Enums;

enum Currency: string
{
    case SAR = 'SAR';
    case AED = 'AED';
    case EGP = 'EGP';
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';

    public function label(): string
    {
        return match ($this) {
            self::SAR => 'Saudi Riyal',
            self::AED => 'UAE Dirham',
            self::EGP => 'Egyptian Pound',
            self::USD => 'US Dollar',
            self::EUR => 'Euro',
            self::GBP => 'British Pound',
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
        };
    }

    public static function isValid(string $code): bool
    {
        return collect(self::cases())->contains(fn($c) => $c->value === $code);
    }
}
