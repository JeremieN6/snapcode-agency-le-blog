<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')] // Vérifie si l'utilisateur est connecté et a le rôle ROLE_ADMIN
class DataCleaningController extends AbstractController
{
    #[Route('/admin/clean-posts', name: 'admin_clean_posts')]
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

        $this->addFlash('success', 'Les articles ont été correctement nettoyés.');
        return $this->redirectToRoute('admin');

    }
}