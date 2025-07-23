<?php

namespace App\Controller;

use App\Service\PexelsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TestPexelsController extends AbstractController
{
    private PexelsService $pexelsService;

    public function __construct(PexelsService $pexelsService)
    {
        $this->pexelsService = $pexelsService;
    }

    #[Route('/test-pexels', name: 'test_pexels')]
    public function testPexels(): JsonResponse
    {
        $imageUrl = $this->pexelsService->getImageFromPexels('Chelsea Football');
        return $this->json(['image_url' => $imageUrl]);
    }
}
