<?php

namespace App\Controller;

use App\Service\OpenAIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TestOpenAIController extends AbstractController
{
    private OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    #[Route('/test-openai', name: 'test_openai')]
    public function testOpenAI(): JsonResponse
    {
        $article = $this->openAIService->generateBlogArticle(
            'Développement Web',
            ['Titre existant 1', 'Titre existant 2']
        );

        return $this->json(['article' => $article]);
    }
}
