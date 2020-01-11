<?php

namespace App\EventSubscriber;

use App\Service\Cache;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ApiCacheSubscriber implements EventSubscriberInterface
{
    const CACHE_PREFIX = "api_";
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var array
     */
    protected $cachedRoutes = ['create_user', 'cashin_user', 'cashout_user'];

    /**
     * @var CacheItem
     */
    private $cacheValue;

    /**
     * ApiCacheSubscriber constructor.
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }


    public function onRequestEvent(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!in_array($request->attributes->get("_route"), $this->cachedRoutes)) {
            return;
        }

        $content = json_decode($request->getContent());
        if (!isset($content->id)) {
            return;
        }

        $itemId = static::CACHE_PREFIX . $content->id;

        $this->cacheValue = $this->cache->getCache()->getItem($itemId);

        if (!$this->cacheValue->isHit()) {
            return;
        }

        $response = new Response($this->cacheValue->get(), 200, ['Content-Type' => 'application/json']);
        $event->setResponse($response);
    }

    public function onResponseEvent(ResponseEvent $event)
    {
        if (null == $this->cacheValue) {
            return;
        }

        $response = $event->getResponse();
        if ($this->isResponseError($response)) {
            return;
        }

        $this->cacheValue->set($response->getContent());
        $this->cache->getCache()->save($this->cacheValue);
    }

    public function isResponseError(Response $response): bool
    {
        $responseContent = json_decode($response->getContent());

        return $responseContent->status && $responseContent->status === 'error';
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class  => 'onRequestEvent',
            ResponseEvent::class => 'onResponseEvent'
        ];
    }
}
