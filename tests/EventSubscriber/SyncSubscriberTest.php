<?php

namespace App\Tests\EventSubscriber;


use App\Service\Cache;
use App\Service\BillingException;
use App\Tests\WebTestCase;

class SyncSubscriberTest extends WebTestCase
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

    public function testSyncFail()
    {
        $request = [
            "name"      => "sync",
            "id"        => "123456",
            "timestamp" => "2019-01-01",
            "session"   => "advstyuci123",
            "token"     => "1234567898765432-TEST-2"
        ];
        $this->client->request('POST', "/billing", [], [], [], json_encode($request));
        $expect = [
            "id"    => $request["id"],
            "error" => [
                "code"    => BillingException::TOKEN_NOT_FOUND,
                "message" => "user not found"
            ]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $this->client->getResponse()->getContent());
    }

    public function testSyncSuccess()
    {
        $request = [
            "name"      => "sync",
            "id"        => "123456",
            "timestamp" => "2019-01-01",
            "session"   => "advstyuci123",
            "token"     => $this->token
        ];
        $this->client->request('POST', "/billing", [], [], [], json_encode($request));
        $expect = [
            "id"      => $request["id"],
            "balance" => [
                "value"   => 1000,
                "version" => 0
            ]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $this->client->getResponse()->getContent());
    }
}
