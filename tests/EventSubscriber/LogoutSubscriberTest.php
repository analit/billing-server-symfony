<?php

namespace App\Tests\EventSubscriber;

use App\Service\BillingException;
use App\Service\Cache;
use App\Tests\WebTestCase;

class LogoutSubscriberTest extends WebTestCase
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

    public function testLogoutSuccess()
    {
        $request = [
            "name"      => "logout",
            "id"        => "123456",
            "timestamp" => "2019-01-01",
            "session"   => "advstyuci123",
            "token"     => $this->token
        ];
        $this->client->request('POST', "/billing", [], [], [], json_encode($request));
        $expect = [
            "id" => $request["id"]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $this->client->getResponse()->getContent());

        $this->client->request('POST', "/billing", [], [], [], json_encode(array_replace($request, ['id' => 1234567])));
        $expect = [
            'id'    => '1234567',
            'error' => [
                'code'    => BillingException::TOKEN_NOT_FOUND,
                'message' => 'user not found'
            ]
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expect), $this->client->getResponse()->getContent());
    }
}
