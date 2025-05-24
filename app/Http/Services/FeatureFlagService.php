<?php

namespace App\Http\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Log;

use App\Models\FeatureFlag;

class FeatureFlagService
{
    protected const CACHE_KEY_PREFIX = 'feature_flag_';
    protected const TTL = null;

    public function __construct(private Cache $cache) {}

    private function setCache(string $key, bool $enabled): void
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $key;

        if (self::TTL !== null) {
            $this->cache->put($cacheKey, $enabled, self::TTL);
        } else {
            $this->cache->forever($cacheKey, $enabled);
        }
    }

    public function isEnabled(string $key): bool
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $key;

        if ($this->cache->has($cacheKey)) {
            return (bool) $this->cache->get($cacheKey);
        }

        $enabled = FeatureFlag::where('key', $key)->value('enabled') ?? false;

        // Store in cache persistently or for a short TTL
        if (self::TTL !== null) {
            $this->cache->put($cacheKey, $enabled, self::TTL);
        } else {
            $this->cache->forever($cacheKey, $enabled);
        }

        return (bool) $enabled;
    }

    public function enable(string $key): void
    {
        FeatureFlag::updateOrCreate(['key' => $key], ['enabled' => true]);

        $this->setCache($key, true);
        Log::info("Enabled feature flag: {$key}");
    }


    public function disable(string $key): void
    {
        FeatureFlag::updateOrCreate(['key' => $key], ['enabled' => false]);

        $this->setCache($key, false);
        Log::info("Disabled feature flag: {$key}");
    }

}
