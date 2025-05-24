<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Http\Services\FeatureFlagService;

class ToggleWebhookIngestion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:toggle-webhook-ingestion {status : enable or disable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle webhook ingestion';

    /**
     * Execute the console command.
     */
    public function handle(FeatureFlagService $flags)
    {
        $status = $this->argument('status'); // enable / disable
    
        Log::info("Toggling webhook ingestion to: {$status}");

        if ($status === 'enable') {
            $flags->enable('webhook_ingestion');
            $this->info('Webhook ingestion enabled.');
        } else {
            $flags->disable('webhook_ingestion');
            $this->info('Webhook ingestion disabled.');
        }
    }
    
}
