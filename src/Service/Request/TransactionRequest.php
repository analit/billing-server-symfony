<?php


namespace App\Service\Request;

use JMS\Serializer\Annotation\Type;

class TransactionRequest extends Request
{
    /**
     * @var int
     * @Type("int")
     */
    private $minus;

    /**
     * @var int
     * @Type("int")
     */
    private $plus;

    /**
     * @return int
     */
    public function getMinus(): int
    {
        return $this->minus;
    }

    /**
     * @return int
     */
    public function getPlus(): int
    {
        return $this->plus;
    }
}