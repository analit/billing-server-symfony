<?php


namespace App\EventSubscriber;


use Symfony\Component\HttpFoundation\Response;

/**
 * Class BillingCacheSubscriber
 * @package App\EventSubscriber
 */
class BillingCacheSubscriber extends ApiCacheSubscriber
{
    const CACHE_PREFIX = "billing_";

    protected $cachedRoutes = ['billing'];

    public function isResponseError(Response $response): bool
    {
        $responseContent = json_decode($response->getContent());

        return isset($responseContent->error);
    }

}