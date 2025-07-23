<?php

namespace App\Service;

use App\Service\PexelsService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OpenAIService
{
    private $apiKey;
    private $httpClient;
    private PexelsService $pexelsService;

    public function __construct(ParameterBagInterface $parameterBag, HttpClientInterface $httpClient, PexelsService $pexelsService)
    { 
        $this->apiKey = $parameterBag->get('OPENAI_API_KEY');
        $this->httpClient = $httpClient;
        $this->pexelsService = $pexelsService;
    }

    public function generateBlogArticle(string $categoryName, array $existingTitles): string
    {
        // Trouver un sujet unique en rapport avec la catégorie
        $subject = $this->findUniqueSubject($categoryName, $existingTitles);

        if (!$subject) {
            return 'Aucun sujet unique trouvé.';
        }

        // Récupérer l'image via Pexels
        $imageUrl = $this->pexelsService->getImageFromPexels($categoryName);
        $fallbackImageUrl = 'https://placehold.co/900x600'; // Valeur par défaut

        if (!$imageUrl) {
            $imageUrl = $fallbackImageUrl;
        }

        // Construire le prompt OpenAI
        $prompt = sprintf(
            "Génère moi un article de blog pour la catégorie %s sur le sujet : '%s'." .
                "L'article fera entre 500 et 600 mots max. Ce sera un article mentionnant les meilleurs conseils que tu peux donner sur ce sujet en tant qu'expert." .
                " Tu feras en sorte de manière subtile que c'est mon agence qui prodigue ces conseils, et que si ce sujet est important pour le lecteur, il n'a qu'à contacter l'agence." .
                " Tu n'es pas obligé de présenter l'entreprise directement au début de l'article, tu peux le faire n'importe ou dans l'article. Par contre si tu inventes un nom d'entreprise, fait en sorte qu'il soit réalise. Pas de nom fictif, ou qui fait trop exemple. ".
                " L'article sera découpé entre 4 et 6 paragraphes avec des sous-titres qui doivent être insérés dans des balises <h3 class='wp-block-heading'> (pas de hashtags ou de Markdown) et des paragraphes dans des balises <p>." .
                " Ne mets pas dans les titres h3 des mots comme “contexte”, “conclusion”, en gros des mots qui illustrent la structure de l’article. ".
                " Il y aura une url d'image illustrative à générer et à placer dans le corps de l'article avec ce code : <figure class='wp-block-image alignwide size-large'><img src='%s' alt='Image pour %s' decoding='async' width='1024' height='683' class='wp-image-2338'/></figure>." .
                " Il y aura une seconde url d'image à générer et celle ci sera à mettre dans le champs dédié en base de donnée dans le champs featured_image dans l'entité Posts." .
                // " Utilise ton éditeur de code pour qu'on puisse voir si tu as bien mis les balises paragraphes, les balises h3 et les classes dans les balises h3.".
                " Enfin, assure-toi que l'article est unique, qu'il n'y a pas de doublon de titre dans la base.",
            $categoryName,
            $subject,
            $imageUrl,
            $categoryName // Description par défaut de l'image
        );

        // Data structure for the chat model endpoint
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 3500,
            'temperature' => 0.7,
        ];

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
                'json' => $data,
            ]);

            $content = $response->getContent();
            $decodedResponse = json_decode($content, true);

            if (isset($decodedResponse['choices'][0]['message']['content'])) {
                return $decodedResponse['choices'][0]['message']['content'];
            } else {
                return 'Une erreur est survenue dans la réponse de l\'API.';
            }
        } catch (\Exception $e) {
            return 'Erreur lors de la requête à l\'API : ' . $e->getMessage();
        }
    }

    private function findUniqueSubject(string $categoryName, array $existingTitles): string
    {
        // Logic to find a unique subject not covered before in existing articles
        // This is a simplified approach, you can integrate more advanced logic if needed

        $subject = 'Nouveau sujet intéressant sur ' . $categoryName;

        // Check if subject already exists
        if (in_array($subject, $existingTitles)) {
            return null; // No unique subject found
        }

        return $subject;
    }
}
