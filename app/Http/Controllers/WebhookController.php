<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessWebhookPayload;

class WebhookController extends Controller
{
    public function handle(Request $request, string $bank)
    {
        Log::info("Webhook received: {$bank} {$request->getContent()}");
        
        try {
            // publish to rabbitmq
            ProcessWebhookPayload::dispatch($bank, $request->getContent())->onQueue('webhook_queue');
            Log::debug("Webhook dispatched to rabbitmq: {$bank}");
        } catch (Exception $e) {
            Log::error("Error processing webhook: {$e->getMessage()}");
            return response()->json(['message' => 'Error processing webhook'], 500);
        }
    
        return response()->json(['message' => 'Webhook payload queued'], 202);
    }
}
