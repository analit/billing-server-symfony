<?php


namespace App\Event;

use App\Service\Request\RequestInterface;
use App\Service\Response\ResponseInterface;

interface EventInterface
{
    public function getResponse(): ResponseInterface;

    public function getRequest(): RequestInterface;
}