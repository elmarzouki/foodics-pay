<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GenerateTransferXml;
use App\Http\Services\Transfer\TransferRequestDTO;

class GenerateTransferXmlTest extends TestCase
{
    public function test_job_generates_and_stores_xml()
    {
        Storage::fake('local');
        $dto = TransferRequestDTO::fromArray([
            'amount' => 100,
            'currency' => 'SAR',
            'sender_account' => 'SA6980000204608016212908',
            'receiver_account' => 'SA6980000204608016211111',
            'receiver_name' => 'Jane Doe',
            'bank_code' => 'FDCSSARI',
            'notes' => ['debt'],
            'payment_type' => '421',
            'charge_details' => 'RB',
        ]);
        
        $job = new GenerateTransferXml($dto);
        $job->handle();

        Storage::disk('local')->assertExists("transfers/{$dto->reference}.xml");
    }
}
