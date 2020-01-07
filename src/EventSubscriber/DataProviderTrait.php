<?php


namespace App\EventSubscriber;


use App\Service\DataProvider;

trait DataProviderTrait
{
    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * LoginSubscriber constructor.
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }
}