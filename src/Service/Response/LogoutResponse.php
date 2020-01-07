<?php


namespace App\Service\Response;
use JMS\Serializer\Annotation\Type;

class LogoutResponse implements ResponseInterface
{
    /**
     * @var string
     * @Type("string")
     */
    private $id;

    /**
     * Response constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }
}