<?php

namespace App\Repository;

use App\Entity\Agence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Agence>
 */
class AgenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agence::class);
    }

    // Recherche les agences par nom, ville ou code postal
    public function rechercher(?string $q = null): array 
    {
        $qb = $this->createQueryBuilder('a') 
        ->orderBy('a.ville', 'ASC'); 

        if ($q) {
            $qb->andWhere('a.nom LIKE :q OR a.ville LIKE :q OR a.codePostal LIKE :q') 
                ->setParameter('q', '%' . $q . '%'); 
        }

        return $qb->getQuery() 
            ->getResult(); 
    }



//    /**
//     * @return Agence[] Returns an array of Agence objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Agence
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
