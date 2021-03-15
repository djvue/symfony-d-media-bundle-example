<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    public function __construct(
    ) {
    }

    #[Route('', name: 'page_main', methods: ['GET', 'HEAD'])]
    public function filter(Request $request): Response
    {
        $data = [];
        return $this->render('index.html.twig', $data);
    }
}
