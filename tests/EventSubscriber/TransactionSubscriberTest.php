<?php

namespace App\Tests\EventSubscriber;


use App\Service\BillingException;
use App\Tests\WebTestCase;

class TransactionSubscriberTest extends WebTestCase
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

    static function getBaseRequest()
    {
        return [
            "name"      => "transaction",
            "id"        => "123456789",
            "timestamp" => "2016-03-02T22:51:45+00:00",
            "session"   => "4db895f0e0c911e58ac80242ac110009",
            "plus"      => 0,
            "minus"     => 200,
            "token"     => "123456"
        ];
    }

    public function testMethodNotFound()
    {
        $request = self::getBaseRequest();
        $request['name'] = 'blablabla';
        $this->client->request('POST', "/billing", [], [], [], json_encode($request));
        $expect = [
            "id"    => $request['id'],
            "error" => [
                "code"    => BillingException::OTHER_ERROR,
                "message" => "method not found"
            ]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $this->client->getResponse()->getContent());

        $request = [];
        $this->client->request('POST', "/billing", [], [], [], json_encode($request));
        $expect = [
            "id"    => "undefined",
            "error" => [
                "code"    => BillingException::OTHER_ERROR,
                "message" => json_decode($this->client->getResponse()->getContent())->error->message
            ]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $this->client->getResponse()->getContent());
    }

    public function testTransactionLowBalance()
    {
        $request = self::getBaseRequest();
        $request = array_replace($request, ['minus' => 1100, 'token' => $this->token]);
        $this->client->request('POST', "/billing", [], [], [], json_encode($request));
        $expect = [
            "id"    => $request["id"],
            "error" => [
                "code"    => BillingException::LOW_BALANCE,
                "message" => ""
            ]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $this->client->getResponse()->getContent());
    }

    public function testTransactionSuccess()
    {
        $request = self::getBaseRequest();
        $request = array_replace($request, ['token' => $this->token]);
        $this->client->request('POST', "/billing", [], [], [], json_encode($request));
        $expect = [
            "id"      => $request["id"],
            "balance" => [
                "value"   => 800,
                "version" => 1
            ]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $this->client->getResponse()->getContent());

        $request = array_replace($request, ['token' => $this->token, 'plus' => 1000, 'id' => $request['id'] . "1"]);
        $this->client->request('POST', "/billing", [], [], [], json_encode($request));
        $expect = [
            "id"      => $request["id"],
            "balance" => [
                "value"   => 1600,
                "version" => 3
            ]
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expect), $this->client->getResponse()->getContent());
    }
}
