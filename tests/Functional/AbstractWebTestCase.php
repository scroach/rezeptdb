<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AbstractWebTestCase extends WebTestCase
{
    /** @var KernelBrowser */
    public $client;

    public function setUp(): void
    {
        $this->loginRick();
    }

    public function loginRick(): void
    {
        $this->createAuthenticatedClient('rick');
    }

    public function loginMorty(): void
    {
        $this->createAuthenticatedClient('morty');
    }

    private function createAuthenticatedClient(string $username): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW' => 'supersecurepassword!',
        ]);
        $this->client->catchExceptions(false);
    }

    public function assertResponseContains(string $string): void
    {
        self::assertStringContainsString($string, (string) $this->client->getResponse()->getContent());
    }

    public function assertSuccessMessage(string $string): void
    {
        self::assertStringContainsString($string, $this->client->getCrawler()->filter('.ui.positive.message')->text());
    }

    public function assertWarningMessage(string $string): void
    {
        self::assertStringContainsString($string, $this->client->getCrawler()->filter('.ui.warning.message')->text());
    }

    public function assertErrorMessage(string $string): void
    {
        self::assertStringContainsString($string, $this->client->getCrawler()->filter('.ui.negative.message')->text());
    }
}
