<?php


namespace App\Service\Request;

use JMS\Serializer\Annotation\Type;

class Request implements RequestInterface
{
    /**
     * @var string
     * @Type("string")
     */
    private $name;

    /**
     * @var string
     * @Type("string")
     */
    private $id;

    /**
     * @var string
     * @Type("string")
     */
    private $timestamp;

    /**
     * @var string
     * @Type("string")
     */
    private $session;

    /**
     * @var string
     * @Type("string")
     */
    private $token;

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}