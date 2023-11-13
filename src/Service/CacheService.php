<?php

namespace App\Service;

use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheService
{
    public function __construct(
        private readonly TagAwareCacheInterface $cache,
        private readonly SerializerInterface $serializer
    ) {
    }

    public function getOrCache(string $cacheKey, mixed $data, array $tags = [], int $expiry = 600)
    {
        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            $cacheItem->set($data);
            if (!empty($tags)) {
                $cacheItem->tag($tags);
            }
            $cacheItem->expiresAfter($expiry);
            $this->cache->save($cacheItem);
        }

        // Return the cached data
        return $cacheItem->get();
    }
}
