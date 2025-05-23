<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Services\FeatureFlagService;
use Illuminate\Support\Facades\Cache;
use Database\Seeders\FeatureFlagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeatureFlagServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FeatureFlagSeeder::class);
    }

    public function test_webhook_ingestion_flag_is_seeded()
    {
        $this->assertDatabaseHas('feature_flags', [
            'key' => 'webhook_ingestion',
            'enabled' => true,
        ]);
    }

    public function test_enable_and_check_flag()
    {
        
        $cache = Cache::store('array'); // in-memory
        $flags = new FeatureFlagService($cache);
    
        $flags->enable('webhook_ingestion');
        $this->assertTrue($flags->isEnabled('webhook_ingestion'));
    
        $flags->disable('webhook_ingestion');
        $this->assertFalse($flags->isEnabled('webhook_ingestion'));
    }    
}
