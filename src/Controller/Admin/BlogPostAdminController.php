<?php

namespace App\Controller\Admin;

use App\Service\BlogPostGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class BlogPostAdminController extends AbstractController
{
    #[Route('/admin/generate-post', name: 'admin_generate_post')]
    public function generatePost(
        BlogPostGenerator $blogPostGenerator,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        try {
            // Utiliser le service pour générer l'article
            $post = $blogPostGenerator->generatePost();

            // Sauvegarder l'article généré en base
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Article généré avec succès : ' . $post->getTitle());
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la génération de l\'article : ' . $e->getMessage());
        }

        // Rediriger vers la page principale de l'admin
        return $this->redirectToRoute('admin');
    }
}
