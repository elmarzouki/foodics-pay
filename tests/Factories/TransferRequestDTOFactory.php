<?php

namespace Tests\Factories;

use App\Http\Services\Transfer\TransferRequestDTO;
use Faker\Factory as Faker;

class TransferRequestDTOFactory
{
    public static function make(array $overrides = []): TransferRequestDTO
    {
        $faker = Faker::create();

        $base = [
            'amount' => $faker->randomFloat(2, 10, 9999),
            'currency' => 'SAR',
            'sender_account' => 'SA' . $faker->numerify('###000000###########'),
            'receiver_account' => 'SA' . $faker->numerify('###000000###########'),
            'receiver_name' => $faker->name(),
            // Randomized realistic options
            // which should be provided from enum class
            // the same way we did for currency
            'bank_code' => $faker->randomElement(['FDCSSARI', 'ACMEBANK1', 'BANKXYZ9']),
            'notes' => [$faker->sentence(3)],
            'payment_type' => $faker->randomElement(['421', '98', '01', null]),
            'charge_details' => $faker->randomElement(['RB', 'RC', 'RD', null]),
        ];

        return TransferRequestDTO::fromArray(array_merge($base, $overrides));
    }
}
