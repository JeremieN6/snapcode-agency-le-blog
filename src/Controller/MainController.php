<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Repository\CategoriesRepository;
use App\Repository\PostsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(
        Request $request,
        CategoriesRepository $categoriesRepository,
        PostsRepository $postsRepository,
        String $slug = null
    ): Response
    {
        $categoryPost = $categoriesRepository->findAll();

        // Récupérer les catégories avec le nombre de posts
        $allCategoriesWithCount = $categoriesRepository->findAllWithPostCount();

        // Récupérer les 3 articles les plus récents
        $recentPosts = $postsRepository->findThreeMostRecentPosts();

        // Définir la locale pour la requête
        $request->setLocale('fr');

        $favoritePosts = $postsRepository->findBy(['isFavorite' => true]);

        $theCategoryPost = $categoriesRepository->findOneBy(['slug' => $slug]);

        $latestPostsByCategory = $postsRepository->findLatestPostsByCategory();

        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
    
        $menuCategorie = [
            'Développement Web' => 'developpement-web',
            'Projets Clients' => 'projets-clients',
            'Conseils aux Entreprises' => 'conseils-aux-entreprises',
            'Marketing Digital et Stratégie Web' => 'marketing-digital-et-strategie-web',
            'Design et Expérience Utilisateur (UX)' => 'design-et-experience-utilisateur-ux',
            'Fonctionnalités et Performances Web' => 'fonctionnalites-et-performances-web',
            'Sécurité et Conformité' => 'securite-et-conformite',
            'Transformation Numérique' => 'transformation-numerique'
        ];

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'latestPostsByCategory' => $latestPostsByCategory,
            'recentPosts' => $recentPosts,
            'theCategoryPost' => $theCategoryPost,
            'categorieListWithCount' => $allCategoriesWithCount,
            'categoryPost' => $categoryPost,
            'searchForm' => $searchForm->createView(),
            'favoritePosts' => $favoritePosts,
            'menuCategorie' => $menuCategorie
        ]);
    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request, PostsRepository $postsRepository, CategoriesRepository $categoriesRepository): Response
    {

        $categoryPost = $categoriesRepository->findAll();

        $favoritePosts = $postsRepository->findBy(['isFavorite' => true]);

        // Récupérer les catégories avec le nombre de posts
        $allCategoriesWithCount = $categoriesRepository->findAllWithPostCount();

        // Récupérer la valeur du formulaire
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
    
        $query = null;

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();
            $query = $data['query'];
            // dump($query); // Vérifiez que la requête est bien récupérée
        } else {
            // dump('Form not submitted or not valid'); // Vérifiez l'état du formulaire
        }
    
        if ($query) {
            $posts = $postsRepository->searchByKeyword($query);
        } else {
            $posts = [];
        }

        // Récupérer les catégories associées à chaque post
        $postCategories = [];
        foreach ($posts as $post) {
            $postCategories[$post->getId()] = $post->getCategories();
        }

        $menuCategorie = [
            'Développement Web' => 'developpement-web',
            'Projets Clients' => 'projets-clients',
            'Conseils aux Entreprises' => 'conseils-aux-entreprises',
            'Marketing Digital et Stratégie Web' => 'marketing-digital-et-strategie-web',
            'Design et Expérience Utilisateur (UX)' => 'design-et-experience-utilisateur-ux',
            'Fonctionnalités et Performances Web' => 'fonctionnalites-et-performances-web',
            'Sécurité et Conformité' => 'securite-et-conformite',
            'Transformation Numérique' => 'transformation-numerique'
        ];
       
        return $this->render('main/search_results.html.twig', [
            'query' => $query,
            'posts' => $posts,
            'postCategories' => $postCategories,
            'searchForm' => $searchForm->createView(),
            'categoryPost' => $categoryPost,
            'favoritePosts' => $favoritePosts,
            'menuCategorie' => $menuCategorie,
            'categorieListWithCount' => $allCategoriesWithCount,
        ]);
    }

    #[Route('/categorie/{slug}', name: 'app_category')]
    public function category(
        Request $request,
        CategoriesRepository $categoriesRepository,
        PostsRepository $postsRepository,
        String $slug
    ): Response
    {
        $categoryPost = $categoriesRepository->findAll();

        // Récupérer les catégories avec le nombre de posts
        $allCategoriesWithCount = $categoriesRepository->findAllWithPostCount();

        // Définir la locale pour la requête
        $request->setLocale('fr');

        $favoritePosts = $postsRepository->findBy(['isFavorite' => true]);

        // Récupérer la catégorie par son slug
        $theCategoryPost = $categoriesRepository->findOneBy(['slug' => $slug]);

        if (!$theCategoryPost) {
            throw $this->createNotFoundException('Catégorie non trouvé !');
        }

        // Récupérer les articles de cette catégorie
        // $currentPost = $postsRepository->findBy(['categories' => $category]);

        // Débogage: Vérifiez que la catégorie est récupérée correctement
        // dump($category);

        // Récupérer les articles de cette catégorie
        $currentPost = $postsRepository->createQueryBuilder('p')
            ->join('p.categories', 'c')
            ->where('c.id = :categoryId')
            ->setParameter('categoryId', $theCategoryPost->getId())
            ->getQuery()
            ->getResult();

        // Débogage: Vérifiez que les articles sont récupérés correctement
        // dump($currentPost);

        // Compter le nombre d'articles dans cette catégorie
        $postCount = $postsRepository->createQueryBuilder('p')
        ->select('count(p.id)')
        ->join('p.categories', 'c')
        ->where('c.id = :categoryId')
        ->setParameter('categoryId', $theCategoryPost->getId())
        ->getQuery()
        ->getSingleScalarResult();

        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);

        $menuCategorie = [
            'Développement Web' => 'developpement-web',
            'Projets Clients' => 'projets-clients',
            'Conseils aux Entreprises' => 'conseils-aux-entreprises',
            'Marketing Digital et Stratégie Web' => 'marketing-digital-et-strategie-web',
            'Design et Expérience Utilisateur (UX)' => 'design-et-experience-utilisateur-ux',
            'Fonctionnalités et Performances Web' => 'fonctionnalites-et-performances-web',
            'Sécurité et Conformité' => 'securite-et-conformite',
            'Transformation Numérique' => 'transformation-numerique'
        ];

        
        return $this->render('main/category.html.twig', [
            'controller_name' => 'HomeController',
            'theCategoryPost' => $theCategoryPost,
            'categoryPost' => $categoryPost,
            'postsList' => $currentPost,
            'postCount' => $postCount,
            'categorieListWithCount' => $allCategoriesWithCount,
            'searchForm' => $searchForm->createView(),
            'favoritePosts' => $favoritePosts,
            'menuCategorie' => $menuCategorie
        ]);
    }

    #[Route('/article/{slug}', name: 'article_show')]
    public function article(
        Request $request,
        CategoriesRepository $categoriesRepository,
        PostsRepository $postsRepository,
        string $slug
    ): Response
    {
        $categoryPost = $categoriesRepository->findAll();

        // Récupérer les catégories avec le nombre de posts
        $allCategoriesWithCount = $categoriesRepository->findAllWithPostCount();

        // Récupérer la catégorie par son slug
        $theCategoryPost = $categoriesRepository->findOneBy(['slug' => $slug]);

        $favoritePosts = $postsRepository->findBy(['isFavorite' => true]);

        // Définir la locale pour la requête
        $request->setLocale('fr');

        // Récupérer l'article par son slug
        $post = $postsRepository->findOneBy(['slug' => $slug]);

        if (!$post) {
            throw $this->createNotFoundException('Article non trouvé !');
        }

        // Récupérer les catégories "conseil entreprise" et "Projets Clients"
        $categories = $categoriesRepository->findBy(['slug' => ['conseils-aux-entreprises', 'projets-clients', 'marketing-digital-et-strategie-web']]);

        // Récupérer des articles de ces catégories
        $relatedPosts = [];
        foreach ($categories as $category) {
            $categoryPosts = $postsRepository->createQueryBuilder('p')
                ->join('p.categories', 'c')
                ->where('c.id = :categoryId')
                ->setParameter('categoryId', $category->getId())
                ->getQuery()
                ->getResult();
            $relatedPosts = array_merge($relatedPosts, $categoryPosts);
        }

        // Mélanger les articles et sélectionner les 4 premiers
        shuffle($relatedPosts);
        $relatedPosts = array_slice($relatedPosts, 0, 4);

        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);

        $menuCategorie = [
            'Développement Web' => 'developpement-web',
            'Projets Clients' => 'projets-clients',
            'Conseils aux Entreprises' => 'conseils-aux-entreprises',
            'Marketing Digital et Stratégie Web' => 'marketing-digital-et-strategie-web',
            'Design et Expérience Utilisateur (UX)' => 'design-et-experience-utilisateur-ux',
            'Fonctionnalités et Performances Web' => 'fonctionnalites-et-performances-web',
            'Sécurité et Conformité' => 'securite-et-conformite',
            'Transformation Numérique' => 'transformation-numerique'
        ];

        return $this->render('main/article.html.twig', [
            'categorieListWithCount' => $allCategoriesWithCount,
            'post' => $post,
            'searchForm' => $searchForm->createView(),
            'relatedPosts' => $relatedPosts,
            'categoryPost' => $categoryPost,
            'theCategoryPost' => $theCategoryPost,
            'favoritePosts' => $favoritePosts,
            'menuCategorie' => $menuCategorie
        ]);
    }
}
