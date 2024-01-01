<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CheckController extends AbstractController
{
    #[Route(path: '/lcheck', name: 'global_lcheck')]
    public function lCheck(): Response
    {
        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent('OK');

        return $response;
    }
}
