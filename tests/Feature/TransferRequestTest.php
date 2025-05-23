<?php

namespace Tests\Feature;

use Tests\TestCase;

class TransferRequestTest extends TestCase
{
    public function test_valid_payload_passes_validation()
    {
        $response = $this->postJson('/api/v1/transfer', [
            'reference' => '0d5d37eb-6eb5-4c35-a07a-4f8ef8bb1c5c',
            'date' => '2025-06-01T12:00:00+03:00',
            'amount' => 177.39,
            'currency' => 'SAR',
            'sender_account' => 'SA6980000204608016212908',
            'receiver_account' => 'SA6980000204608016211111',
            'receiver_name' => 'Jane Doe',
            'bank_code' => 'FDCSSARI',
            'notes' => ['Note 1', 'Note 2'],
            'payment_type' => '421',
            'charge_details' => 'RB'
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
