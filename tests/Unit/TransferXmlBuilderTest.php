<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Services\Transfer\TransferXmlBuilder;
use Tests\Factories\TransferRequestDTOFactory;
use Carbon\Carbon;

class TransferXmlBuilderTest extends TestCase
{
    public function test_generates_valid_xml()
    {
        $dto = TransferRequestDTOFactory::make([
            'reference' => 'e0f4763d-28ea-42d4-ac1c-c4013c242105',
            'date' => Carbon::parse('2025-02-25 06:33:00+03:00'),
            'amount' => 177.39,
            'currency' => 'SAR',
            'notes' => ['Note A', 'Note B'],
            'payment_type' => '421',
            'charge_details' => 'RB',
        ]);

        $xml = TransferXmlBuilder::fromDTO($dto)->build();

        $this->assertStringContainsString('<PaymentRequestMessage>', $xml);
        $this->assertStringContainsString("<Reference>{$dto->reference}</Reference>", $xml);
        $this->assertStringContainsString('<Amount>177.39</Amount>', $xml);
        $this->assertStringContainsString("<Currency>{$dto->currency}</Currency>", $xml);
        $this->assertStringContainsString('<Note>Note A</Note>', $xml);
    }

    public function test_skips_optional_tags()
    {
        $dto = TransferRequestDTOFactory::make([
            'notes' => [],
            'payment_type' => '99',
            'charge_details' => 'SHA',
        ]);

        $xml = TransferXmlBuilder::fromDTO($dto)->build();

        $this->assertStringNotContainsString('<Notes>', $xml);
        $this->assertStringNotContainsString('<PaymentType>', $xml);
        $this->assertStringNotContainsString('<ChargeDetails>', $xml);
    }
}
