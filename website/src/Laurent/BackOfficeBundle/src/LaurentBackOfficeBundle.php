<?php

declare(strict_types=1);

namespace Laurent\BackOfficeBundle;

use Laurent\BackOfficeBundle\Service\AdminRoleDefinition;
use Spipu\CoreBundle\AbstractBundle;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;

class LaurentBackOfficeBundle extends AbstractBundle
{
    public function getRolesHierarchy(): RoleDefinitionInterface
    {
        return new AdminRoleDefinition();
    }
}
