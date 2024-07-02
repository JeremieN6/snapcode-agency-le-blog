<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DataCleaningController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/clean-posts', name: 'clean_posts')]
    public function cleanPosts(EntityManagerInterface $entityManager): Response
    {
        $connection = $entityManager->getConnection();

        $sqls = [
            "UPDATE posts SET content = REPLACE(REPLACE(REPLACE(content, '&lt;', '<'), '&gt;', '>'), '&amp;', '&')",
            "UPDATE posts SET content = REPLACE(content, '<br>', '')",
            "UPDATE posts SET content = REPLACE(content, '<br/>', '')",
            "UPDATE posts SET content = REPLACE(content, '&nbsp;', '')"
        ];

        foreach ($sqls as $sql) {
            $stmt = $connection->prepare($sql);
            $stmt->executeStatement();
        }

        return new Response('Posts cleaned successfully.');

        // // Ajouter un message flash
        // $this->addFlash('success', 'Les articles ont été correctement nettoyés selon les requêtes.');

        // // Rediriger vers une page spécifique après le nettoyage
        // return $this->redirectToRoute('app_main'); // Remplacez 'app_main' par la route vers laquelle vous souhaitez rediriger l'utilisateur
    }
}