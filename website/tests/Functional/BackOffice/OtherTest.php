<?php

declare(strict_types=1);

namespace App\Tests\Functional\BackOffice;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class OtherTest extends BackOfficeAbstract
{
    public function testAdmin(): void
    {
        $client = static::createPantherClient();

        $this->navigateLogin($client);
        $this->navigateLink($client, 'Admin', 'Configurations');
        $this->navigateLink($client, 'Admin', 'Process Tasks');
        $this->navigateLink($client, 'Admin', 'Process Logs');
        $this->navigateLink($client, 'Admin', 'Admin Users');
    }

    public function testMyProfile(): void
    {
        $client = static::createPantherClient();

        $this->navigateLogin($client);
        $this->navigateDirectLink($client, 'My Profile');
    }
}
