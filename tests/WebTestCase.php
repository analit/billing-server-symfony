<?php

namespace App\Tests;

use App\Entity\User;
use App\Service\Cache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{

    /**
     * @var KernelBrowser
     */
    protected $client;

    protected function setUp():void
    {
        $this->client = static::createClient();
    }

    protected function clearData(): void
    {
        static::$container->get("database_connection")->exec("delete from user");
        static::$container->get(Cache::class)->getCache()->clear();
    }
}
