<?php

declare(strict_types=1);

namespace Laurent\CoreBundle\Controller;

use Laurent\CoreBundle\Entity\AdminUser;
use Spipu\CoreBundle\Controller\AbstractController as SpipuAbstractController;

/**
 * @method AdminUser getUser()
 */
abstract class AbstractController extends SpipuAbstractController
{
}
