<?php


namespace App\Service;


class BillingException extends \Exception
{
    const TOKEN_NOT_FOUND = "TOKEN_NOT_FOUND";
    const LOW_BALANCE = "LOW_BALANCE";
    const OTHER_ERROR = "OTHER_ERROR";
    /**
     * @var string
     */
    private $errorCode;

    /**
     * WLException constructor.
     * @param string $code
     * @param string $message
     */
    public function __construct(string $code, string $message = "")
    {
        $this->errorCode = $code;
        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }


}