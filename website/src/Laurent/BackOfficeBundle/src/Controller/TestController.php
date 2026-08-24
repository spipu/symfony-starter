<?php

declare(strict_types=1);

namespace Laurent\BackOfficeBundle\Controller;

use Laurent\CoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TestController extends AbstractController
{
    #[Route(path: '/test', name: 'admin_test', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function test(Request $request): Response
    {
        $submittedData = null;
        if ($request->isMethod('POST')) {
            $submittedData = $request->request->all();
        }

        return $this->render(
            '@LaurentBackOffice/test/test.html.twig',
            [
                'submittedData' => $submittedData,
            ]
        );
    }
}
