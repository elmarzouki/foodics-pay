<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Http\Services\Transfer\TransferRequestDTO;
use App\Http\Services\Transfer\TransferXmlBuilder;

class GenerateTransferXml implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public TransferRequestDTO $dto;

    /**
     * Create a new job instance.
     */
    public function __construct(TransferRequestDTO $dto)
    {
        $this->dto = $dto;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $xml = TransferXmlBuilder::fromDTO($this->dto)->build();
        Log::info("Generated XML: {$xml}");

        Storage::put("transfers/{$this->dto->reference}.xml", $xml);
        Log::info('Transfer XML generated successfully.', ['reference' => $this->dto->reference]);
    }
}
