<?php

namespace App\EventSubscriber;

use App\Event\TransactionEvent;
use App\Service\BillingException;
use App\Service\Request\TransactionRequest;
use App\Service\Response\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransactionSubscriber implements EventSubscriberInterface
{
    use DataProviderTrait;

    public function onTransaction(TransactionEvent $event)
    {
        /** @var TransactionRequest $request */
        $request = $event->getRequest();

        $user = $this->dataProvider->getUserByToken($request->getToken());

        if (!$user) {
            throw new BillingException(BillingException::OTHER_ERROR, "user not found!");
        }

        if ($user->getBalance() < $request->getMinus()){
            throw new BillingException(BillingException::LOW_BALANCE);
        }

        $user->addBalance(-$request->getMinus());
        $user->addBalance($request->getPlus());

        /** @var Response $response */
        $response = $event->getResponse();
        $response->setBalance($user->getBalance(), $user->getVersion());

        $this->dataProvider->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            TransactionEvent::class => 'onTransaction',
        ];
    }
}
