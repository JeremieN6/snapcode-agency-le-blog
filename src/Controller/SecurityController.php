<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Repository\CategoriesRepository;
use App\Repository\PostsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils, 
        Request $request, 
        CategoriesRepository $categoriesRepository, 
        String $slug = null,
        PostsRepository $postsRepository): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

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

        // Récupérer la catégorie par son slug
        $categoryPost = $categoriesRepository->findOneBy(['slug' => $slug]);

        $favoritePosts = $postsRepository->findBy(['isFavorite' => true]);

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error,
            'searchForm' => $searchForm->createView(),
            'menuCategorie' => $menuCategorie,
            'categoryPost' => $categoryPost,
            'favoritePosts' => $favoritePosts,
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
