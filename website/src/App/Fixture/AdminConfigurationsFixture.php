<?php

declare(strict_types=1);

namespace App\Fixture;

use Spipu\ConfigurationBundle\Service\ConfigurationManager;
use Spipu\CoreBundle\Fixture\FixtureInterface;
use Spipu\CoreBundle\Service\Environment;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

/**
 * @SuppressWarnings(PMD.UnusedPrivateMethod)
 */
class AdminConfigurationsFixture implements FixtureInterface // phpcs:disable Generic.Files.LineLength.TooLong
{
    private Environment $environment;

    private ConfigurationManager $manager;

    public function __construct(Environment $environment, ConfigurationManager $manager)
    {
        $this->environment = $environment;
        $this->manager = $manager;
    }

    public function getOrder(): int
    {
        return 10;
    }

    public function getCode(): string
    {
        return 'spipu-configuration';
    }

    public function load(OutputInterface $output): void
    {
        $envCode = $this->environment->getCurrentCode();
        $output->writeln("Update Configuration - $envCode");

        $configuration = $this->{'get' . ucfirst($envCode) . 'Configuration'}();
        $configuration = array_merge($this->getGlobalConfiguration(), $configuration);

        foreach ($configuration as $key => $value) {
            switch ($this->manager->getField($key)->getCode()) {
                case 'password':
                    $this->manager->setPassword($key, $value);
                    break;

                case 'encrypted':
                    $this->manager->setEncrypted($key, $value);
                    break;

                case 'file':
                    try {
                        $tmpFilename = tempnam('/tmp', 'fixture');
                        unlink($tmpFilename);
                        copy($value, $tmpFilename);
                        $tmpFile = new UploadedFile($tmpFilename, basename($value), mime_content_type($value), UPLOAD_ERR_OK, true);
                        $this->manager->setFile($key, $tmpFile);
                    } catch (Throwable $e) {
                        echo "Error during configuration file save\n" . $e->getMessage() . "\n";
                    }
                    break;

                default:
                    $this->manager->set($key, $value);
                    break;
            }
        }
    }

    public function remove(OutputInterface $output): void
    {
        $output->writeln("Remove Configuration is disabled");
    }

    private function getGlobalConfiguration(): array
    {
        return [
        ];
    }

    private function getDevConfiguration(): array
    {
        return [
            'app.email.sender'              => 'no-reply@starter.lxd',
            'app.website.url'               => 'https://starter.lxd/',

            'process.failed.email'          => 'task-errors@starter.lxd',
            'process.archive.keep_number'   => '50',
            'process.task.automatic_rerun'  => '0',
            'process.task.can_kill'         => '1',
        ];
    }

    private function getPreprodConfiguration(): array
    {
        return [
        ];
    }

    private function getProdConfiguration(): array
    {
        return [
        ];
    }
}
