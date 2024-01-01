<?php

declare(strict_types=1);

namespace App\Service;

use Spipu\ConfigurationBundle\Service\ConfigurationManager;
use Spipu\CoreBundle\Service\Environment;
use Spipu\UiBundle\Entity\Menu\Item;
use Spipu\UiBundle\Service\Menu\DefinitionInterface;

/**
 * @SuppressWarnings(PMD.ExcessiveMethodLength)
 */
class MenuDefinition implements DefinitionInterface // phpcs:disable Generic.Files.LineLength.TooLong
{
    private ?Item $mainItem = null;
    private ConfigurationManager $configurationManager;
    private Environment $environment;

    public function __construct(
        ConfigurationManager $configurationManager,
        Environment $environment
    ) {
        $this->configurationManager = $configurationManager;
        $this->environment = $environment;
    }

    private function build(): void
    {
        $this->mainItem = new Item($this->configurationManager->get('app.website.name'), '', 'app_home');

        $this->mainItem
            ->setIcon('wrench', $this->environment->getCurrentColor(), $this->environment->getCurrentName())
            ->setCssClass('navbar-dark bg-menu-app')
            ->addChild('spipu.ui.page.home', 'app-home', 'app_home')
                ->setIcon('home')
                ->setACL(true)
                ->getParentItem()
            ->addChild('app.menu.admin')
                ->setIcon('tools')
                ->addChild('spipu.configuration.page.admin.list', 'spipu-configuration-admin', 'spipu_configuration_admin_list')
                    ->setACL(true, 'ROLE_ADMIN_MANAGE_CONFIGURATION_SHOW')
                    ->getParentItem()
                ->addChild('spipu.process.page.admin.task.list', 'spipu-process-admin-task', 'spipu_process_admin_task_list')
                    ->setACL(true, 'ROLE_ADMIN_MANAGE_PROCESS_SHOW')
                    ->getParentItem()
                ->addChild('spipu.process.page.admin.log.list', 'spipu-process-admin-log', 'spipu_process_admin_log_list')
                    ->setACL(true, 'ROLE_ADMIN_MANAGE_PROCESS_SHOW')
                    ->getParentItem()
                ->addChild('app.menu.admin_user', 'spipu-user-admin', 'spipu_user_admin_list')
                    ->setAcl(true, 'ROLE_ADMIN_MANAGE_USER_SHOW')
                    ->getParentItem()
                ->getParentItem()

            ->addChild('spipu.user.page.profile.show', 'spipu-user-profile', 'spipu_user_profile_show')
                ->setACL(true)
                ->setIcon('user')
                ->getParentItem()
            ->addChild('spipu.user.page.security.log_in', 'spipu-user-login', 'spipu_user_security_login')
                ->setACL(false)
                ->setIcon('sign-in-alt')
                ->getParentItem()
            ->addChild('spipu.user.page.security.log_out', 'spipu-user-logout', 'spipu_user_security_logout')
                ->setACL(true)
                ->setIcon('sign-out-alt')
                ->getParentItem()
        ;
    }

    public function getDefinition(): Item
    {
        if (!$this->mainItem) {
            $this->build();
        }

        return $this->mainItem;
    }
}
