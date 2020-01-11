<?php


namespace App\Tests\EventSubscriber;


use App\Entity\User;
use App\Service\BillingException;
use App\Tests\WebTestCase;

class CacheSubscriberTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->clearData();
    }

    public function testApiCache()
    {
        $this->client->request("POST", "/api/create-user", [], [], [], json_encode([
            'currency' => 'USD',
            'balance'  => 1000,
            'id'       => 123456,
            'token'    => '123456'
        ]));

        $expectedCreateUser = [
            "status"   => "success",
            "message"  => "user was created",
            "currency" => "USD",
            "balance"  => "1000",
            "token"    => "123456"
        ];

        $actual = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($expectedCreateUser, $actual);

        $this->client->request("POST", "/api/cashin-user", [], [], [], json_encode([
            "token"  => "123456",
            "amount" => 150,
            "id"     => 123456
        ]));

        $actual = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($expectedCreateUser, $actual);

        $this->client->request("POST", "/api/cashin-user", [], [], [], json_encode([
            "token"  => "123457", /** not exists */
            "amount" => 150,
            "id"     => 123457
        ]));

        $expectedError = [
            "status"  => "error",
            "message" => "user not found"
        ];

        $actual = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($expectedError, $actual);

        $this->client->request("POST", "/api/cashin-user", [], [], [], json_encode([
            "token"  => "123456", /** real */
            "amount" => 150,
            "id"     => 123457
        ]));

        $expectedCashIn = [
            "status"   => "success",
            "message"  => "balance was updated",
            "token"    => "123456",
            "currency" => "USD",
            "balance"  => 1150
        ];

        $actual = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($expectedCashIn, $actual);
    }

    public function testBillingCache()
    {
        $this->client->request("POST", "/api/create-user", [], [], [], json_encode([
            'currency' => 'USD',
            'balance'  => 1000,
            'id'       => 123456,
            'token'    => '123456'
        ]));

        $this->client->request("POST", "/billing", [], [], [], json_encode([
            "name"      => "login",
            "id"        => 123456,
            "timestamp" => "2019-01-01",
            "session"   => "abcdefghi",
            "token"     => "123456"
        ]));

        $expectedLogin = [
            "id"      => 123456,
            "user"    => [
                "id"       => (string)static::$container
                    ->get("doctrine.orm.entity_manager")
                    ->getRepository(User::class)
                    ->findOneBy(['token' => '123456'])
                    ->getId(),
                "currency" => "USD",
            ],
            "balance" => [
                "value"   => 1000,
                "version" => 0
            ]
        ];

        $actual = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($expectedLogin, $actual);

        $this->client->request("POST", "/billing", [], [], [], json_encode([
            "name"      => "sync",
            "id"        => 123456,
            "timestamp" => "2019-01-01",
            "session"   => "abcdefghi",
            "token"     => "123456"
        ]));

        $actual = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($expectedLogin/** login from cache */, $actual);

        $this->client->request("POST", "/billing", [], [], [], json_encode([
            "name"      => "sync",
            "id"        => 123457,
            "timestamp" => "2019-01-01",
            "session"   => "abcdefghi",
            "token"     => "123457"/** not exists token */
        ]));

        $expectedError = [
            "id"    => 123457,
            "error" => [
                "code"    => BillingException::TOKEN_NOT_FOUND,
                "message" => "user not found"
            ]
        ];

        $actual = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($expectedError, $actual);

        $this->client->request("POST", "/billing", [], [], [], json_encode([
            "name"      => "sync",
            "id"        => 123457,
            "timestamp" => "2019-01-01",
            "session"   => "abcdefghi",
            "token"     => "123456"/** exists token */
        ]));

        $expectedSync = [
            "id"      => 123457,
            "balance" => [
                "value"   => 1000,
                "version" => 0
            ]
        ];

        $actual = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($expectedSync, $actual);
    }
}