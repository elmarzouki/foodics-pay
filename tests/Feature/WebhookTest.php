<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessWebhookPayload;
use Tests\Factories\WebhookPayloadFactory;
  
class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_payload_is_queued()
    {
        Queue::fake();
    
        $payload = WebhookPayloadFactory::foodics();
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
        $payload = WebhookPayloadFactory::foodics(1000);
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
