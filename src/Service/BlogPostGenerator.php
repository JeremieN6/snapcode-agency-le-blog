<?php

namespace App\Service;

use App\Entity\Posts;
use App\Entity\Categories;
use App\Entity\Users;
use App\Repository\CategoriesRepository;
use App\Repository\PostsRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogPostGenerator extends AbstractController
{
    private OpenAIService $openAIService;
    private PexelsService $pexelsService;
    private PostsRepository $postsRepository;
    private CategoriesRepository $categoriesRepository;

    public function __construct(
        OpenAIService $openAIService,
        PexelsService $pexelsService,
        PostsRepository $postsRepository,
        CategoriesRepository $categoriesRepository
    ) {
        $this->openAIService = $openAIService;
        $this->pexelsService = $pexelsService;
        $this->postsRepository = $postsRepository;
        $this->categoriesRepository = $categoriesRepository;
    }

    public function generatePost(): Posts
    {
        //Étape 0 : On récupère l'utilisateur connecté
        $user = $this->getUser();
        $roles = $user->getRoles();
    
        if (in_array('ROLE_ADMIN', $roles)) {
            // Étape 1 : Choisir une catégorie
            $categories = $this->categoriesRepository->findAll();
            $category = $categories[array_rand($categories)]; // Choix aléatoire
    
            // Étape 2 : Vérifier les titres existants
            $existingTitles = $this->postsRepository->findTitlesByCategory($category->getName());
            $title = $this->generateUniqueTitle($category->getName(), $existingTitles);
            if (!$title) {
                throw new \Exception("Impossible de générer un titre unique.");
            }
    
            // Étape 3 : Construire le prompt pour OpenAI
            $prompt = $this->buildPrompt($title, $category->getName());
    
            // Étape 4 : Générer le contenu
            $content = $this->openAIService->generateBlogArticle($category, $existingTitles);
    
            // Étape 5 : Récupérer les images depuis Pexels
            $bannerImage = $this->pexelsService->getImageFromPexels($title);
            $contentImage = $this->pexelsService->getImageFromPexels($title);
    
            // Remplacer `add_url` dans le contenu par l'URL de l'image intégrée
            $content = str_replace('add_url', $contentImage, $content);
    
            // Étape 6 : Créer l'article
            $post = new Posts();
            $post->setTitle($title)
                ->setSlug($this->generateSlug($title)) // Générer le slug à partir du titre
                ->setUsers($user)
                ->setContent($content)
                ->setFeaturedImage($bannerImage ?: 'https://placehold.co/1350x475') // Image par défaut si aucune image
                ->addCategory($category)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setIsFavorite(rand(0, 1) == 1); // Ajouter une valeur aléatoire pour isFavorite
    
            return $post;
        } else {
            // throw new \Exception('L\'utilisateur doit être connecté et avoir le rôle ADMIN pour générer un article');
            $this->addFlash('danger', 'L\'utilisateur doit être connecté et avoir le rôle ADMIN pour générer un article');
        }
    }
    
    /**
     * Génère un slug à partir du titre de l'article.
     */
    private function generateSlug(string $title): string
    {
        // Remplacer les espaces par des tirets, et supprimer les caractères non alphabétiques, mais garder les majuscules
        $slug = preg_replace('/[^a-zA-Z0-9-]/', '-', $title); // Autorise les lettres majuscules/minuscules et les chiffres
        $slug = trim($slug, '-'); // Supprime les tirets au début et à la fin de la chaîne
        return $slug;
    }

    private function generateUniqueTitle(string $categoryName, array $existingTitles): ?string
    {
        $title = "Conseils pratiques pour $categoryName";
        return in_array($title, $existingTitles) ? null : $title;
    }

    private function buildPrompt(string $title, string $categoryName): string
    {
        return <<<PROMPT
Génère moi un article de blog pour la catégorie $categoryName sur le sujet : $title.
[Ton prompt complet ici, personnalisé avec $categoryName et $title].
PROMPT;
    }
}
