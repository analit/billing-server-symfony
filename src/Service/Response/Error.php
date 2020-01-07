<?php


namespace App\Service\Response;

use JMS\Serializer\Annotation\Type;

class Error
{
    /**
     * @var string
     * @Type("string")
     */
    private $id;

    /**
     * @var ErrorElement
     */
    private $error;

    /**
     * Error constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->error = new ErrorElement();
    }

    public function addError(string $code, string $message)
    {
        $this->error
            ->setCode($code)
            ->setMessage($message);
    }
}