<?php
namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Spipu\CoreBundle\Tests\WebTestCaseTrait as SpipuWebTestCaseTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

global $webTestCaseTraitDataPrimerLoaded;
$webTestCaseTraitDataPrimerLoaded = false;

trait WebTestCaseTrait
{
    use SpipuWebTestCaseTrait;

    protected EntityManagerInterface $entityManager;

    protected function prepareGlobalDataPrimer(KernelInterface $kernel, ContainerInterface $container): void
    {
        $_SERVER['KERNEL_CLASS'] = '\App\Test\Kernel';

        global $webTestCaseTraitDataPrimerLoaded;

        if (!$webTestCaseTraitDataPrimerLoaded) {
            $webTestCaseTraitDataPrimerLoaded = true;
            $this->prepareDataPrimer($kernel, $container);
        }

        exec('php bin/console cache:pool:clear cache.app');
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        if ($this->entityManager === null) {
            $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        }

        return $this->entityManager;
    }
}
