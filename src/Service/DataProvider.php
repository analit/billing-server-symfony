<?php


namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class DataProvider
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * DataProvider constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getUser(int $id): ?User
    {
        return $this->em->find(User::class, $id);
    }

    public function getUserByToken(string $token): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['token' => $token]);
    }

    public function flush(): void
    {
        $this->em->flush();
    }

    public function removeUser(User $user): void
    {
        $this->em->remove($user);
    }


}