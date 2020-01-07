<?php


namespace App\Service;


use App\Event\EventInterface;
use App\Event\SyncEvent;
use App\Event\LoginEvent;
use App\Event\LogoutEvent;
use App\Event\TransactionEvent;
use App\Service\Request\Request as BillingRequest;
use App\Service\Request\TransactionRequest;
use App\Service\Response\LoginResponse;
use App\Service\Response\LogoutResponse;
use App\Service\Response\Response;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;

class EventFactory
{
    const
        METHOD_LOGIN = "login",
        METHOD_TRANSACTION = "transaction",
        METHOD_SYNC = "sync",
        METHOD_LOGOUT = "logout";

    /**
     * @param Request $request
     * @param Serializer $serializer
     * @return EventInterface|null
     * @throws BillingException
     */
    public function createEvent(Request $request, Serializer $serializer): ?EventInterface
    {
        $requestContent = $request->getContent();
        $methodName = json_decode($requestContent)->name ?? "undefined";

        if ($methodName === self::METHOD_LOGIN) {
            $request = $serializer->deserialize($requestContent, BillingRequest::class, 'json');
            $response = new LoginResponse($request->getId());

            return new LoginEvent($request, $response);
        }

        if ($methodName === self::METHOD_SYNC) {
            $request = $serializer->deserialize($requestContent, BillingRequest::class, 'json');
            $response = new Response($request->getId());

            return new SyncEvent($request, $response);
        }

        if ($methodName === self::METHOD_LOGOUT) {
            $request = $serializer->deserialize($requestContent, BillingRequest::class, 'json');
            $response = new LogoutResponse($request->getId());

            return new LogoutEvent($request, $response);
        }

        if ($methodName === self::METHOD_TRANSACTION) {
            $request = $serializer->deserialize($requestContent, TransactionRequest::class, 'json');
            $response = new Response($request->getId());

            return new TransactionEvent($request, $response);
        }

        throw new BillingException(BillingException::OTHER_ERROR, "method not found");
    }
}