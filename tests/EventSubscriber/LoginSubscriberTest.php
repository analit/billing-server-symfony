<?php

namespace App\Tests\EventSubscriber;


use App\Entity\User;
use App\Service\BillingException;
use App\Tests\WebTestCase;

class LoginSubscriberTest extends WebTestCase
{
    private $token = "1234567898765432-TEST";

    protected function setUp(): void
    {
        parent::setUp();
        $this->clearData();

        $this->client->request("POST", "/api/create-user", [], [], [], json_encode([
            'currency' => 'RUB',
            'balance'  => 1000,
            'id'       => 123456,
            'token'    => $this->token
        ]));
    }


    public function testLoginSuccess()
    {
        $login = [
            "name"      => "login",
            "id"        => "123456789",
            "timestamp" => "2019-08-01",
            "session"   => "aser456fr45",
            "token"     => $this->token
        ];
        $this->client->request('POST', "/billing", [], [], [], json_encode($login));
        $expect = [
            "id"      => $login["id"],
            "balance" => [
                "value"   => 1000,
                "version" => 0
            ],
            "user"    => [
                "id"       => (string)static::$container
                    ->get("doctrine.orm.entity_manager")
                    ->getRepository(User::class)
                    ->findOneBy(['token' => $this->token])
                    ->getId(),
                "currency" => "RUB"
            ],
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $this->client->getResponse()->getContent());
    }

    public function testLoginInvalidToken()
    {
        $login = [
            "name"      => "login",
            "id"        => "123456789",
            "timestamp" => "2019-08-01",
            "session"   => "aser456fr45",
            "token"     => '1234567898765432-TEST-2'
        ];
        $this->client->request('POST', "/billing", [], [], [], json_encode($login));

        $responseExpect = [
            "id"   => $login["id"],
            "error" => [
                "code"    => BillingException::TOKEN_NOT_FOUND,
                "message" => ""
            ]

        ];
        $this->assertJsonStringEqualsJsonString(json_encode($responseExpect), $this->client->getResponse()->getContent());
    }
}
