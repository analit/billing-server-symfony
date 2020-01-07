<?php


namespace App\Service\Response;

use App\Entity\User;
use JMS\Serializer\Annotation\Type;

class LoginResponse extends Response
{
    /**
     * @var UserElement
     * @Type("App\Service\Response\UserElement")
     */
    private $user;

    public function populateUser(User $user): self
    {
        $this->user = new UserElement();
        $this->user
            ->setId($user->getId())
            ->setCurrency($user->getCurrency());

        return $this;
    }
}