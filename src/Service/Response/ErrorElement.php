<?php


namespace App\Service\Response;

use JMS\Serializer\Annotation\Type;

class ErrorElement
{
    /**
     * @var string
     * @Type("string")
     */
    private $code;

    /**
     * @var string
     * @Type("string")
     */
    private $message;

    /**
     * @param string $code
     * @return ErrorElement
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param string $message
     * @return ErrorElement
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }


}