<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Faker\Factory as Faker;

class TransferRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
    }

    public function test_valid_payload_passes_validation()
    {
        $response = $this->postJson('/api/v1/transfer', [
            'reference' => $this->faker->uuid,
            'date' => now()->addDays(2)->toIso8601String(),
            'amount' => $this->faker->randomFloat(2, 10, 10000),
            'currency' => 'SAR',
            'sender_account' => 'SA' . $this->faker->numerify('###000000###########'),
            'receiver_account' => 'SA' . $this->faker->numerify('###000000###########'),
            'receiver_name' => $this->faker->name,
            'bank_code' => 'FDCSSARI',
            'notes' => [$this->faker->sentence(2), $this->faker->sentence(3)],
            'payment_type' => '421',
            'charge_details' => 'RB',
        ]);

        $response->assertStatus(202);
    }

    public function test_invalid_payload_returns_422()
    {
        $response = $this->postJson('/api/v1/transfer', [
            'amount' => 'invalid_amount',
            'currency' => 'XYZ'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['currency', 'amount', 'sender_account']);
    }
}
