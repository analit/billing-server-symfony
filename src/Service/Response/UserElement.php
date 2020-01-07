<?php


namespace App\Service\Response;


class UserElement
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $currency;

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }


    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }


}