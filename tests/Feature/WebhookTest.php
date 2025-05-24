<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessWebhookPayload;
use Tests\WebhookPayloadGenerator;
class WebhookTest extends TestCase
{
    public function test_webhook_payload_is_queued()
    {
        Queue::fake();
    
        $payload = "SA6980000204608016212908#20250615156,50#SAR#202506159000001#note/debt payment march/internal_reference/A462JE81";
        $response = $this->call(
            'POST',
            '/api/v1/webhook/foodics',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            $payload
        );
    
        $response->assertStatus(202);
        Queue::assertPushed(ProcessWebhookPayload::class, function ($job) use ($payload) {
            return $job->bank === 'foodics' && $job->payload === $payload;
        });
    }

    public function test_end_to_end_ingestion_of_bulk_payload()
    {
        Queue::fake();
        $payload = WebhookPayloadGenerator::foodics(1000);
        $response = $this->call(
            'POST',
            '/api/v1/webhook/foodics',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            $payload);
        $response->assertStatus(202);
        Queue::assertPushed(ProcessWebhookPayload::class, function ($job) use ($payload) {
            return $job->payload === $payload && $job->bank === 'foodics';
        });
    }
    
}
