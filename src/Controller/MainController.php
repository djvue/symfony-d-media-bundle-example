<?php

namespace App\Controller;

use App\Service\DemoMediasService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MainController extends AbstractController
{
    public function __construct(
        private DemoMediasService $demoMediasService,
        private NormalizerInterface $normalizer,
    ) {
    }

    #[Route('', name: 'page_main', methods: ['GET', 'HEAD'])]
    public function filter(Request $request): Response
    {
        $data = [
            'entities' => $this->demoMediasService->getEntities(),
            'singleMedias' => $this->demoMediasService->getSingleMedias(),
            'multiMedias' => $this->demoMediasService->getMultiMedias(),
        ];
        return $this->render('index.html.twig', $this->normalizer->normalize($data));
    }
}
