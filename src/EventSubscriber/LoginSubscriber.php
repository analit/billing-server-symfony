<?php

namespace App\EventSubscriber;

use App\Event\LoginEvent;
use App\Service\BillingException;
use App\Service\Request\Request;
use App\Service\Response\LoginResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoginSubscriber implements EventSubscriberInterface
{
    use DataProviderTrait;

    public function onLogin(LoginEvent $event): void
    {
        /** @var Request $request */
        $request = $event->getRequest();
        $user = $this->dataProvider->getUserByToken($request->getToken());

        if (!$user) {
            throw new BillingException(BillingException::TOKEN_NOT_FOUND);
        }
        /** @var LoginResponse $response */
        $response = $event->getResponse();
        $response->populateUser($user);
        $response->setBalance($user->getBalance(), $user->getVersion());
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginEvent::class => 'onLogin',
        ];
    }
}
