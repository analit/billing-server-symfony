<?php


namespace App\Event;


use App\Service\Request\RequestInterface;
use App\Service\Response\ResponseInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BaseEvent extends Event implements EventInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * RequestEvent constructor.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }


    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}