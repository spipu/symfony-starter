<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\WebTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class OtherTest extends WebTestCase
{
    public function testAdmin(): void
    {
        $client = static::createPantherClient();

        $this->navigateLogin($client);

        $this->navigateLink($client, 'Admin', 'Configurations');
        $this->navigateLink($client, 'Admin', 'Process Tasks');
        $this->navigateLink($client, 'Admin', 'Process Logs');

        $this->navigateLink($client, 'Admin', 'Admin Users');
        $client->executeScript("document.querySelector('[data-grid-row-id=\"1\"] .dropdown-toggle').click()");
        $this->assertStringContainsString('Show', $client->getCrawler()->getText());
        $client->clickLink('Show');
        $this->assertClientIs200($client);
    }
}

