<?php

namespace App\Service;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheService
{
    public function __construct(
        private readonly TagAwareCacheInterface $cache,
        private readonly SerializerInterface $serializer
    ) {
    }

    private function cache(string $idCache, mixed $data, array $groups): string
    {
        $context = SerializationContext::create()->setGroups($groups);
        $serializer = $this->serializer;
        $response = $this->cache->get($idCache, function (ItemInterface $item) use ($data, $serializer, $context) {
            echo 'cache';
            $item->tag('DetailUserCache');
            $item->expiresAfter(5);

            return $serializer->serialize($data, 'json', $context);
        });

        return $response;
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
