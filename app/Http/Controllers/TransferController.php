<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Http\Services\Transfer\TransferRequestDTO;
use App\Jobs\GenerateTransferXml;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    public function send(TransferRequest $request)
    {
        Log::info("Transfer request received: {$request->getContent()}");
        try {
            $validated = $request->validated();
            $dto = TransferRequestDTO::fromArray($validated);

            GenerateTransferXml::dispatch($dto)->onQueue('transfer_xml_queue');
            Log::info('Transfer data queued.', ['reference' => $dto->reference]);
        } catch (Exception $e) {
            Log::error("Error processing transfer request: {$e->getMessage()}");
            return response()->json(['message' => 'Error processing transfer request'], 500);
        }

        return response()->json([
            'message' => 'Transfer data queued.',
            'reference' => $dto->reference,
        ], 202);
    }
}
