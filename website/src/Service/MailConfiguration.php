<?php

declare(strict_types=1);

namespace App\Service;

use Spipu\ConfigurationBundle\Service\ConfigurationManager;
use Spipu\UserBundle\Service\MailConfigurationInterface;

class MailConfiguration implements MailConfigurationInterface
{
    private ConfigurationManager $manager;

    public function __construct(ConfigurationManager $manager)
    {
        $this->manager = $manager;
    }

    public function getEmailFrom(): string
    {
        return $this->manager->get('app.email.sender');
    }

    public function getEmailTo(string $code): string
    {
        return $this->manager->get('app.email.receive.' . $code);
    }
}
