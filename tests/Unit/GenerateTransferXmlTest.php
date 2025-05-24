<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GenerateTransferXml;
use Tests\Factories\TransferRequestDTOFactory;

class GenerateTransferXmlTest extends TestCase
{
    public function test_job_generates_and_stores_xml()
    {
        Storage::fake('local');
        $dto = TransferRequestDTOFactory::make();
        
        $job = new GenerateTransferXml($dto);
        $job->handle();

        Storage::disk('local')->assertExists("transfers/{$dto->reference}.xml");
    }
}
