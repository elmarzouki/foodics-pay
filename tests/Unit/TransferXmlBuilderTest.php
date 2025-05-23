<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Services\Transfer\TransferXmlBuilder;
use App\Http\Services\Transfer\TransferRequestDTO;
use Carbon\Carbon;

class TransferXmlBuilderTest extends TestCase
{
    public function test_generates_valid_xml()
    {
        $dto = new TransferRequestDTO(
            reference: 'e0f4763d-28ea-42d4-ac1c-c4013c242105',
            date: Carbon::parse('2025-02-25 06:33:00+03:00'),
            amount: 177.39,
            currency: 'SAR',
            senderAccount: 'SA6980000204608016212908',
            receiverAccount: 'SA6980000204608016211111',
            receiverName: 'Jane Doe',
            bankCode: 'FDCSSARI',
            notes: ['Note A', 'Note B'],
            paymentType: '421',
            chargeDetails: 'RB'
        );

        $xml = TransferXmlBuilder::fromDTO($dto)->build();

        $this->assertStringContainsString('<PaymentRequestMessage>', $xml);
        $this->assertStringContainsString('<Reference>e0f4763d-28ea-42d4-ac1c-c4013c242105</Reference>', $xml);
        $this->assertStringContainsString('<Amount>177.39</Amount>', $xml);
        $this->assertStringContainsString('<Currency>SAR</Currency>', $xml);
        $this->assertStringContainsString('<Note>Note A</Note>', $xml);
    }

    public function test_skips_optional_tags()
    {
        $dto = new TransferRequestDTO(
            reference: 'e0f4763d-28ea-42d4-ac1c-c4013c242105',
            date: Carbon::now(),
            amount: 50.00,
            currency: 'SAR',
            senderAccount: 'SA6980000204608016212908',
            receiverAccount: 'SA6980000204608016211111',
            receiverName: 'John Doe',
            bankCode: 'FDCSSARI',
            notes: [],
            paymentType: '99',
            chargeDetails: 'SHA'
        );

        $xml = TransferXmlBuilder::fromDTO($dto)->build();

        $this->assertStringNotContainsString('<Notes>', $xml);
        $this->assertStringNotContainsString('<PaymentType>', $xml);
        $this->assertStringNotContainsString('<ChargeDetails>', $xml);
    }
}
