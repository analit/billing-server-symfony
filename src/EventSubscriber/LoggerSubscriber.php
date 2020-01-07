<?php

namespace App\EventSubscriber;

use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class LoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * LoggerSubscriber constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }


    public function onRequestEvent(RequestEvent $event)
    {
        $requestContent = $event->getRequest()->getContent();
        $this->logger->info($requestContent, ["REQUEST", $event->getRequest()->getRequestUri()]);
    }

    public function onResponseEvent(ResponseEvent $event)
    {
        $responseContent = $event->getResponse()->getContent();
        $this->logger->info($responseContent, ["RESPONSE", $event->getRequest()->getRequestUri()]);
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class  => 'onRequestEvent',
            ResponseEvent::class => 'onResponseEvent'
        ];
    }
}
