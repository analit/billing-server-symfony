<?php
/**
 * Created by PhpStorm.
 * User: serg
 * Date: 25.01.19
 * Time: 11:18
 */

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Cache
 * @package App\Service
 * dir - /tmp/symfony-cache
 */
class Cache
{
    /**
     * @var FilesystemAdapter
     */
    private $cache;

    /**
     * Cache constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->cache = new FilesystemAdapter("", 0, $container->getParameter("kernel.project_dir") . "/var/cache/billing");
    }

    /**
     * @return FilesystemAdapter
     */
    public function getCache(): FilesystemAdapter
    {
        return $this->cache;
    }


}
