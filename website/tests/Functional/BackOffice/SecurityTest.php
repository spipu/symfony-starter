<?php

declare(strict_types=1);

namespace App\Tests\Functional\BackOffice;

use App\Tests\WebTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class SecurityTest extends WebTestCase
{
    protected string $testMode = 'back-office';

    public function testLoginKo(): void
    {
        $client = static::createPantherClient();

        // Home page => redirects to login
        $client->request('GET', '/');
        $this->takeScreenshot();
        $this->assertClientIs200($client);
        $this->assertGreaterThan(0, $client->getCrawler()->selectButton('Log In')->count());

        // Login with wrong credentials
        $client->submit(
            $client->getCrawler()->selectButton('Log In')->form(),
            [
                '_username' => 'unknown_user',
                '_password' => 'wrong_password',
            ]
        );
        $this->takeScreenshot();

        // Invalid credentials alert
        $this->assertClientIs200($client);
        $this->assertClientHasAlert($client, 'Invalid credentials');
    }

    public function testLoginOk(): void
    {
        $client = static::createPantherClient();

        $this->assertLogIn($client, self::ADMIN_USER, self::ADMIN_PASS);
        $this->takeScreenshot();
        $this->assertClientIs200($client);
    }

    public function testLogout(): void
    {
        $client = static::createPantherClient();

        // Log in first
        $this->assertLogIn($client, self::ADMIN_USER, self::ADMIN_PASS);
        $this->assertClientIs200($client);

        // Log out
        $this->logOut($client);
        $this->takeScreenshot();

        // Back to login page
        $this->assertClientIs200($client);
        $this->assertGreaterThan(0, $client->getCrawler()->selectButton('Log In')->count());
    }
}
