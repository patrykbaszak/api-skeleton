<?php

declare(strict_types=1);

namespace App\Tests\E2e\Status;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** @group e2e */
class StatusControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $appVersion;
    private string $appCommitSha;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $this->appVersion = json_decode(file_get_contents(self::getContainer()->getParameter('kernel.project_dir').'/package.json'), true)['version'];
        $this->appCommitSha = file_get_contents(self::getContainer()->getParameter('kernel.project_dir').'/.git/refs/heads/main');

        parent::setUp();
    }

    /** @test */
    public function shouldReturnValidResponse(): void
    {
        $this->client->request('GET', '/api/status');

        $response = $this->client->getResponse();

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $response->headers->get('Content-Type'));
        self::assertEquals($this->appVersion, $response->headers->get('X-Version'));
        self::assertEquals($this->appCommitSha, $response->headers->get('X-Version-Sha'));
        self::assertEquals('{"status":"ok"}', $response->getContent());
    }
}
