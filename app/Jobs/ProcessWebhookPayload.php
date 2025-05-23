<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Http\Services\Webhook\WebhookService;
use App\Http\Services\FeatureFlagService;
use App\Enums\Bank;
use InvalidArgumentException;


class ProcessWebhookPayload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $bank;
    public string $payload;


    /**
     * Create a new job instance.
     */
    public function __construct(string $bank, string $payload)
    {
        $this->bank = $bank;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(WebhookService $service, FeatureFlagService $flags): void
    {
        $status = $flags->isEnabled('webhook_ingestion') ? 'enabled' : 'disabled';
        Log::info("ProcessWebhookPayload: {$status}");
        // check if webhook ingestion is enabled
        if (!$flags->isEnabled('webhook_ingestion')) {
            Log::error("Webhook ingestion is currently disabled.");
            $this->release(30); // retry in 30 seconds
            throw new \Exception("Webhook ingestion is currently disabled.");
        }

        $bank = Bank::fromString($this->bank);
        if (!$bank) {
            Log::warning("Unsupported bank: {$this->bank}");
            throw new InvalidArgumentException("Unsupported bank '{$this->bank}'");
        }

        $parser = $bank->parser();

        $service->process($this->payload, $parser);
    }
}
