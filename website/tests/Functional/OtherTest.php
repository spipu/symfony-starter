<?php
namespace App\Tests\Functional;

use App\Tests\WebTestCase;

class OtherTest extends WebTestCase
{
    public function testAdmin()
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

