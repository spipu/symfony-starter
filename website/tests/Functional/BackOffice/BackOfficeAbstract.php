<?php

declare(strict_types=1);

namespace App\Tests\Functional\BackOffice;

use App\Tests\WebTestCase;
use Symfony\Component\Panther\Client;

abstract class BackOfficeAbstract extends WebTestCase
{
    protected string $testMode = 'back-office';

    protected function navigateLogin(Client $client): void
    {
        $this->assertLogIn($client, self::ADMIN_USER, self::ADMIN_PASS);
        $this->takeScreenshot();
        $this->assertClientIs200($client);
        $client->refreshCrawler();
    }

    protected function navigateLink(Client $client, string $menu, string $subMenu): void
    {
        $client->clickLink($menu);
        $client->clickLink($subMenu);
        $this->takeScreenshot($menu . '-' . $subMenu);
        $this->assertClientIs200($client);
        $client->refreshCrawler();
    }

    protected function navigateDirectLink(Client $client, string $link): void
    {
        $client->clickLink($link);
        $this->takeScreenshot($link);
        $this->assertClientIs200($client);
        $client->refreshCrawler();
    }
}
