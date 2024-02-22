<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
    #[Route('/category', name: 'app_category')]
    public function category(): Response
    {
        return $this->render('main/category.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/article', name: 'app_article')]
    public function article(): Response
    {
        return $this->render('main/article.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
