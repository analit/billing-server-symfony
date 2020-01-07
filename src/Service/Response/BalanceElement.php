<?php


namespace App\Service\Response;

use JMS\Serializer\Annotation\Type;

class BalanceElement
{
    /**
     * @var int
     * @Type("int")
     */
    private $value = 0;

    /**
     * @var int
     * @Type("int")
     */
    private $version = 0;

    /**
     * @param int $value
     * @return BalanceElement
     */
    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param int $version
     * @return BalanceElement
     */
    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }


}