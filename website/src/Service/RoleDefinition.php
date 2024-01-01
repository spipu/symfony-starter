<?php

declare(strict_types=1);

namespace App\Service;

use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;

class RoleDefinition implements RoleDefinitionInterface
{
    public function buildDefinition(): void
    {
        $this->buildDefinitionAdmin();

        Item::load('ROLE_SUPER_ADMIN')
            ->addChild('ROLE_ADMIN_MANAGE_ADMIN')
        ;
    }

    private function buildDefinitionAdmin(): void
    {
        Item::load('ROLE_ADMIN_MANAGE_ADMIN')
            ->setLabel('app.role.admin')
            ->setWeight(40000)
            ->addChild('ROLE_ADMIN_MANAGE_CONFIGURATION')
            ->addChild('ROLE_ADMIN_MANAGE_PROCESS')
            ->addChild('ROLE_ADMIN_MANAGE_USER')
        ;
    }
}
