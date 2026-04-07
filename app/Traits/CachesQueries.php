<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CachesQueries Trait
 *
 * Provides reusable caching methods for services and controllers.
 * Centralizes cache key generation and cache management logic.
 * 
 * This trait is designed to work exclusively with Redis cache driver.
 * All methods support cache tags for better invalidation and organization.
 */
trait CachesQueries
{
    /**
     * Remember a value in cache with a given key and duration.
     * Uses Redis cache tags when provided for better invalidation.
     *
     * @param string $key Cache key
     * @param int $seconds Cache duration in seconds
     * @param callable $callback Callback to execute if cache miss
     * @param array $tags Optional cache tags for grouped invalidation (Redis only)
     * @return mixed
     */
    protected function remember(string $key, int $seconds, callable $callback, array $tags = [])
    {
        try {
            if (!empty($tags)) {
                return Cache::tags($tags)->remember($key, $seconds, $callback);
            }
            return Cache::remember($key, $seconds, $callback);
        } catch (\Exception $e) {
            // If cache fails, execute callback directly
            Log::warning("Cache operation failed, executing callback directly", [
                'key' => $key,
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);
            return $callback();
        }
    }

    /**
     * Forget a cache key.
     * Uses Redis cache tags when provided.
     *
     * @param string $key Cache key to forget
     * @param array $tags Optional cache tags (Redis only)
     * @return bool
     */
    protected function forget(string $key, array $tags = []): bool
    {
        try {
            if (!empty($tags)) {
                return Cache::tags($tags)->forget($key);
            }
            return Cache::forget($key);
        } catch (\Exception $e) {
            Log::warning("Cache forget operation failed", [
                'key' => $key,
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Forget multiple cache keys.
     *
     * @param array $keys Array of cache keys to forget
     * @param array $tags Optional cache tags (Redis only)
     * @return void
     */
    protected function forgetMany(array $keys, array $tags = []): void
    {
        foreach ($keys as $key) {
            $this->forget($key, $tags);
        }
    }

    /**
     * Flush all cache entries with given tags.
     * This is a powerful operation that removes all cached items tagged with the specified tags.
     *
     * @param array $tags Cache tags to flush
     * @return bool
     */
    protected function flushTags(array $tags): bool
    {
        if (empty($tags)) {
            Log::warning("Cannot flush empty tags array");
            return false;
        }

        try {
            Cache::tags($tags)->flush();
            Log::info("Cache tags flushed", ['tags' => $tags]);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to flush cache tags", [
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate a cache key with consistent prefix.
     *
     * @param string ...$parts Key parts to join
     * @return string
     */
    protected function cacheKey(string ...$parts): string
    {
        return implode('.', $parts);
    }
}
