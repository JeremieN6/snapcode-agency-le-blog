<?php

namespace App\Service;


use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ClientException as ExceptionClientException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException as HttpClientException;

class PexelsService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $parameterBag)
    {
        // $this->client = $client;
        $this->httpClient = $httpClient;
        // Assurez-vous de récupérer la bonne clé API de votre configuration
        $this->apiKey = $parameterBag->get('PEXELS_API_KEY'); // Clé API Pexels
        dump($this->apiKey); // Vérifiez que la clé est bien récupérée
    }

    /**
     * Récupère une image depuis Pexels en fonction de la catégorie
     * 
     * @param string $categoryName
     * @return string|null URL de l'image ou null si l'appel échoue
     */
    public function getImageFromPexels(string $categoryName): ?string
    {
        try {
            // Requête vers Pexels API avec Guzzle
            $response = $this->httpClient->request('GET', 'https://api.pexels.com/v1/search', [
                'headers' => [
                    'Authorization' => trim($this->apiKey), // Utilisez la clé API passée en paramètre
                ],
                'query' => [
                    'query' => $categoryName, // Utiliser le nom de la catégorie
                    'per_page' => 1, // Limiter le nombre d'images récupérées à 1
                ]
            ]);

            // Décoder la réponse JSON
            $data = $response->toArray();

            // Debug : afficher la réponse pour diagnostiquer les problèmes
            dump($data);

            // Vérifier si une image a été retournée et extraire l'URL
            if (isset($data['photos'][0]['src']['original'])) {
                return $data['photos'][0]['src']['original'];
            }

            // Si aucune image n'a été trouvée, retourner null
            return null;

        } catch (ExceptionClientException $e) {
            // Gérer les erreurs d'API Pexels
            echo "Erreur API Pexels : " . $e->getMessage();
            return null; // Retourner null en cas d'erreur
        }
    }
}
