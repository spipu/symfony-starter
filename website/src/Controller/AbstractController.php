<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AdminUser;
use Spipu\CoreBundle\Controller\AbstractController as SpipuAbstractController;

/**
 * @method AdminUser getUser()
 */
abstract class AbstractController extends SpipuAbstractController
{
}
