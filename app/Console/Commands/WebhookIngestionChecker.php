<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Services\FeatureFlagService;

class WebhookIngestionChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check {key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the webhook ingestion is enabled';

    /**
     * Execute the console command.
     */
    public function handle(FeatureFlagService $flags)
    {
        $key = $this->argument('key');
        $isEnabled = $flags->isEnabled($key);
        $this->info($isEnabled ? 'enabled' : 'disabled');
    }
}
