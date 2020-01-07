<?php

namespace App\EventSubscriber;

use App\Event\LoginEvent;
use App\Service\BillingException;
use App\Service\DataProvider;
use App\Service\WLException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Service\Request\LoginRequest;
use App\Service\Response\LoginResponse;

class LoginSubscriber implements EventSubscriberInterface
{
    use DataProviderTrait;

    public function onLogin(LoginEvent $event): void
    {
        /** @var LoginRequest $request */
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
