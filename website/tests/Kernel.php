<?php

declare(strict_types=1);

namespace App\Tests;

use Spipu\CoreBundle\Tests\Kernel as SpipuKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Kernel extends SpipuKernel
{
    protected function build(ContainerBuilder $container): void
    {
        $container->getParameterBag()->set('APP_SETTINGS_APP_CODE', 'dev');
        $container->getParameterBag()->set('APP_SETTINGS_MAILER_DSN', 'null://default');
    }
}