<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FeatureFlag;

class FeatureFlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FeatureFlag::updateOrCreate(['key' => 'webhook_ingestion'], ['enabled' => true]);
    }
}
