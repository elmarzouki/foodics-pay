<?php

namespace Tests\Factories;

use App\Http\Services\Transaction\TransactionDTO;
use App\Enums\Currency;
use Faker\Factory;
use Carbon\Carbon;

class TransactionDTOFactory
{
    public static function makeTransactionDTO(array $overrides = []): TransactionDTO
    {
        $faker = Factory::create();

        return new TransactionDTO(
            reference: $overrides['reference'] ?? $faker->uuid,
            bankAccountId: $overrides['bankAccountId'] ?? 'SA' . $faker->numerify('##########################'),
            amountCents: $overrides['amountCents'] ?? $faker->numberBetween(100, 50000),
            currency: $overrides['currency'] ?? $faker->randomElement(Currency::cases()),
            date: $overrides['date'] ?? Carbon::instance($faker->dateTimeThisYear()),
            meta: $overrides['meta'] ?? $faker->randomElements([
                'note' => $faker->words(3, true),
                'category' => $faker->word,
                'internal_reference' => strtoupper($faker->bothify('??###')),
            ], rand(1, 3))
        );
    }
}
