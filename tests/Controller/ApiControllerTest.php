<?php

namespace App\Tests;

use App\Service\Cache;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        static::$container->get("database_connection")->exec("delete from user");
        static::$container->get(Cache::class)->getCache()->clear();
    }

    public function testCreateUser()
    {
        $this->client->request('POST', "/api/create-user");
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(json_decode($this->client->getResponse()->getContent())->status, 'error');

        $this->client->request('POST', "/api/create-user", [], [], [], json_encode([
            'currency' => 'EUR',
            'id'       => 12345678,
            'token'    => '1234567898765432-TEST'
        ]));
        $content = $this->client->getResponse()->getContent();
        $response = json_decode($content);
        $this->assertEquals($response->status, "success");

        /* from cache */
        $this->client->request('POST', "/api/create-user", [], [], [], json_encode([
            'currency' => 'EUR',
            'id'       => 12345678,
            'token'    => '1234567898765432-TEST-2' /* other token */
        ]));
        $this->assertJsonStringEqualsJsonString($content, $this->client->getResponse()->getContent());
    }

    public function testDeleteUser()
    {
        $this->client->request('POST', "/api/create-user", [], [], [], json_encode([
            'balance' => 100, 'currency' => 'EUR', 'id' => 556, 'token' => '1234567898765432-TEST'
        ]));
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($response->status, "success");

        $this->client->request('POST', "/api/delete-user", [], [], [], json_encode(['token' => '1234567898765432-TEST']));
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($response->status, 'success');

        $this->client->request('POST', "/api/delete-user", [], [], [], json_encode(['token' => '1234567898765432-TEST-2']));
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($response->status, 'error');
        $this->assertContains('not found', $response->message);
    }

    public function testCashInUser()
    {
        $token = '1234567898765432-TEST';
        $this->client->request('POST', "/api/create-user", [], [], [], json_encode([
            'balance' => 100, 'currency' => 'EUR', 'id' => 12345, 'token' => $token
        ]));
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($response->status, "success");
        $content = $this->client->getResponse()->getContent();

        /* from cache */
        $this->client->request('POST', "/api/cashin-user", [], [], [], json_encode([
            'token'   => '1234567898765432-TEST',
            'balance' => 100,
            'id'      => 12345 // == create-user
        ]));
        $this->assertJsonStringEqualsJsonString($content, $this->client->getResponse()->getContent());

        $this->client->request('POST', "/api/cashin-user", [], [], [], json_encode(['token' => $token]));
        $this->assertEquals(json_decode($this->client->getResponse()->getContent())->status, 'error');

        $this->client->request('POST', "/api/cashin-user", [], [], [], json_encode(['amount' => 200]));
        $this->assertEquals(json_decode($this->client->getResponse()->getContent())->status, 'error');

        $this->client->request('POST', "/api/cashin-user");
        $this->assertEquals(json_decode($this->client->getResponse()->getContent())->status, 'error');

        for ($i = 0; $i < 3; $i++) {
            $this->client->request('POST', "/api/cashin-user", [], [], [], json_encode([
                'token'  => $token,
                'amount' => 100,
                'id'     => 2222
            ]));
        }

        $this->assertEquals(json_decode($this->client->getResponse()->getContent())->balance, 200);
    }

    public function testCashOutUser()
    {
        $token = '1234567898765432-TEST';
        $createUserData = json_encode([
            'balance' => 200, 'currency' => 'EUR', 'id' => 555, 'token' => $token
        ]);
        $this->client->request('POST', "/api/create-user", [], [], [], $createUserData);
        $content = $this->client->getResponse()->getContent();

        /** from cache */
        $this->client->request('POST', "/api/cashout-user", [], [], [], json_encode([
            'token'  => $token,
            'amount' => 100,
            'id'     => 555 // == id create-user
        ]));
        $this->assertJsonStringEqualsJsonString($content, $this->client->getResponse()->getContent());

        $this->client->request('POST', "/api/cashout-user", [], [], [], json_encode([
            'token'  => $token,
            'amount' => 100,
            'id'     => 551
        ]));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        for ($i = 0; $i < 5; $i++) {
            $this->client->request('POST', "/api/cashout-user", [], [], [], json_encode([
                'token'  => $token,
                'amount' => 10,
                'id'     => 552
            ]));
        }

        $this->assertEquals(json_decode($this->client->getResponse()->getContent())->balance, 90);

        $this->client->request('POST', "/api/get-balance", [], [], [], json_encode(['token' => $token, 'id' => 56235]));
        $this->assertEquals(json_decode($this->client->getResponse()->getContent())->balance, 90);

        $this->client->request('POST', "/api/cashout-user", [], [], [], json_encode([
            'token'  => $token,
            'amount' => 300,
            'id'     => 558
        ]));
        $this->assertEquals(json_decode($this->client->getResponse()->getContent())->status, 'error');
        $this->assertEquals(json_decode($this->client->getResponse()->getContent())->message, 'low balance');
    }
}
