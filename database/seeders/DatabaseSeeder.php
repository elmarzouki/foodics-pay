<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;
use Database\Seeders\FeatureFlagSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            FeatureFlagSeeder::class,
        ]);
    }
}
