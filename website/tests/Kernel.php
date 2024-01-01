<?php
namespace App\Tests;

use Spipu\CoreBundle\Tests\Kernel as SpipuKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class Kernel extends SpipuKernel
{
    protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder
    ): void {
        parent::configureContainer($container, $loader, $builder);

        $container->parameters()->set('APP_SETTINGS_APP_CODE', 'dev');
        $container->parameters()->set('APP_SETTINGS_MAILER_DSN', 'null://default');
    }
}
