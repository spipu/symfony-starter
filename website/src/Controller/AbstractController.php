<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AdminUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;

/**
 * @method AdminUser getUser()
 */
abstract class AbstractController extends BaseAbstractController
{
    protected function addFlashTrans(string $type, string $message, array $params = []): void
    {
        $this->addFlash($type, $this->trans($message, $params));
    }

    protected function trans(string $message, array $params = []): string
    {
        return $this->container->get('translator')->trans($message, $params);
    }

    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
                'translator',
            ];
    }
}
