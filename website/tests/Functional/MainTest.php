<?php
namespace App\Tests\Functional;

use App\Tests\WebTestCase;

class MainTest extends WebTestCase
{
    public function testAdminKo()
    {
        $client = static::createPantherClient();

        // Home page => go to log-in
        $client->request('GET', '/');
        $this->takeScreenshot();
        $this->assertClientIs200($client);

        $client->clickLink('Log In');
        $this->takeScreenshot();
        $this->assertClientIs200($client);

        $this->assertGreaterThan(0, $client->getCrawler()->selectButton('Log In')->count());

        // Login Submit
        $client->submit(
            $client->getCrawler()->selectButton('Log In')->form(),
            [
                '_username' => 'test_user',
                '_password' => 'password'
            ]
        );
        $this->takeScreenshot();

        // Invalid credentials
        $this->assertClientIs200($client);
        $this->assertClientHasAlert($client, 'Invalid credentials');
    }

    public function testAdminOk()
    {
        $client = static::createPantherClient();

        $this->assertLogIn($client, self::ADMIN_USER, self::ADMIN_PASS);
        $this->takeScreenshot();
        $this->assertClientIs200($client);
    }
}
