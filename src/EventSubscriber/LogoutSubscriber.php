<?php

namespace App\EventSubscriber;

use App\Event\LogoutEvent;
use App\Service\BillingException;
use App\Service\Request\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogoutSubscriber implements EventSubscriberInterface
{
    use DataProviderTrait;

    public function onLogout(LogoutEvent $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();

        $user = $this->dataProvider->getUserByToken($request->getToken());

        if (!$user) {
            throw new BillingException(BillingException::TOKEN_NOT_FOUND, "user not found");
        }

        $this->dataProvider->removeUser($user);
        $this->dataProvider->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }
}
