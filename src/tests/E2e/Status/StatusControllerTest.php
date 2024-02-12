<?php

declare(strict_types=1);

namespace App\Tests\E2e\Status;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** @group e2e */
class StatusControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    /** @test */
    public function shouldReturnValidResponse(): void
    {
        $this->client->request('GET', '/status');

        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $response->headers->get('Content-Type'));
        self::assertEquals(getenv('APP_VERSION'), $response->headers->get('X-Version'));
        self::assertEquals(getenv('APP_COMMIT_SHA'), $response->headers->get('X-Version-Sha'));
        self::assertEquals('{"status":"ok"}', $response->getContent());
    }
}
