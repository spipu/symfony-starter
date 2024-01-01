<?php

declare(strict_types=1);

namespace App\Controller;

use Spipu\CoreBundle\Service\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MainController extends AbstractController
{
    #[Route(path: '/', name: 'app_home', methods: 'GET')]
    public function home(
        Environment $environment
    ): Response {
        return $this->render(
            '/main/home.html.twig',
            [
                'environment' => $environment,
            ]
        );
    }
}
