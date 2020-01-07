<?php

namespace App\EventSubscriber;

use App\Event\SyncEvent;
use App\Service\BillingException;
use App\Service\Request\Request;
use App\Service\Response\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SyncSubscriber implements EventSubscriberInterface
{
    use DataProviderTrait;

    public function onGetBalance(SyncEvent $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();
        $user = $this->dataProvider->getUserByToken($request->getToken());

        if (!$user) {
            throw new BillingException(BillingException::TOKEN_NOT_FOUND, "user not found");
        }
        /** @var Response $response */
        $response = $event->getResponse();
        $response->setBalance($user->getBalance(), $user->getVersion());
    }

    public static function getSubscribedEvents()
    {
        return [
            SyncEvent::class => 'onGetBalance',
        ];
    }
}
