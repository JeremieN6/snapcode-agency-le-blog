<?php

namespace App\Repository;

use App\Entity\Categories;
use App\Entity\Posts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Posts>
 *
 * @method Posts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Posts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Posts[]    findAll()
 * @method Posts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Posts::class);
    }

    public function findLatestPostsByCategory(): array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Posts p
            INNER JOIN p.categories c
            WHERE p.createdAt IN (
                SELECT MAX(p2.createdAt)
                FROM App\Entity\Posts p2
                INNER JOIN p2.categories c2
                WHERE c2.id = c.id
                GROUP BY c2.id
            )'
        );

        return $query->getResult();
    }

    public function findThreeMostRecentPosts(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    public function searchByKeyword($keyword)
    {
        return $this->createQueryBuilder('p')
            ->where('p.title LIKE :keyword OR p.content LIKE :keyword')
            ->setParameter('keyword', '%'.$keyword.'%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function findByCategory(string $categoryName)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.categories', 'c') // Correctement 'categories' ici
            ->andWhere('c.name = :categoryName')
            ->setParameter('categoryName', $categoryName)
            ->getQuery()
            ->getResult();
    }

    public function findAllTitles(): array
    {
        // Effectue la requête pour récupérer les titres
        $query = $this->createQueryBuilder('p')
            ->select('p.title')
            ->getQuery();

        // Exécute la requête et retourne le tableau des titres
        $result = $query->getResult();

        // Convertir le résultat pour ne récupérer que les titres sous forme de tableau de chaînes
        return array_map(function ($post) {
            return $post['title'];
        }, $result);
    }

    public function findTitlesByCategory(string $categoryName): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.categories', 'c') // Assure-toi que 'category' est le bon nom de la relation dans Posts
            ->andWhere('c.name = :categoryName') // Utilise la propriété 'name' de Categories
            ->setParameter('categoryName', $categoryName)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Posts[] Returns an array of Posts objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Posts
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
