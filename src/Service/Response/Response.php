<?php


namespace App\Service\Response;

use JMS\Serializer\Annotation\Type;

class Response implements ResponseInterface
{
    /**
     * @var string
     * @Type("string")
     */
    private $id;

    /**
     * @Type("App\Service\Response\BalanceElement")
     * @var BalanceElement
     */
    private $balance;

    /**
     * Response constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->balance = new BalanceElement();
    }

    public function setBalance(int $value, int $version): self
    {
        $this->balance
            ->setValue($value)
            ->setVersion($version);

        return $this;
    }


}