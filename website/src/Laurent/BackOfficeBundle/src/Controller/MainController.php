<?php

declare(strict_types=1);

namespace Laurent\BackOfficeBundle\Controller;

use Laurent\CoreBundle\Controller\AbstractController;
use Spipu\CoreBundle\Service\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MainController extends AbstractController
{
    #[Route(path: '/', name: 'admin_home', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN')]
    public function home(
        Environment $environment
    ): Response {
        return $this->render(
            '@LaurentBackOffice/main/home.html.twig',
            [
                'environment' => $environment,
            ]
        );
    }
}
