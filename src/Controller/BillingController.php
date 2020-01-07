<?php

namespace App\Controller;

use App\Service\BillingException;
use App\Service\EventFactory;
use App\Service\Response\Error;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BillingController extends AbstractController
{
    /**
     * @Route("/billing", name="billing", methods={"POST"})
     * @param Request $request
     * @param Serializer $serializer
     * @param EventDispatcherInterface $eventDispatcher
     * @param EventFactory $eventFactory
     * @return JsonResponse
     */
    public function index(
        Request $request,
        Serializer $serializer,
        EventDispatcherInterface $eventDispatcher,
        EventFactory $eventFactory
    )
    {
        $receiverResponse = null;
        $event = null;
        try {
            $event = $eventFactory->createEvent($request, $serializer);
            $eventDispatcher->dispatch($event);
            $receiverResponse = $event->getResponse();
        } catch (BillingException $e) {
            $receiverResponse = new Error($this->getBillingRequestId($request));
            $receiverResponse->addError($e->getErrorCode(), $e->getMessage());
        } catch (\Exception $e) {
            $receiverResponse = new Error($this->getBillingRequestId($request));
            $receiverResponse->addError(BillingException::OTHER_ERROR, $e->getMessage());
        }

        return new JsonResponse($serializer->serialize($receiverResponse, 'json'), Response::HTTP_OK, [], true);
    }

    private function getBillingRequestId(Request $request)
    {
        $content = json_decode($request->getContent());

        return $content->id ?? "undefined";
    }


}
