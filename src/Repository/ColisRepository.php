<?php

namespace App\Repository;

use App\Entity\Colis;
use App\Enum\StatutColis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Colis>
 */
class ColisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Colis::class);
    }

    // Rechercher des colis à partir de plusieurs critères facultatifs.
    public function rechercher(
        ?string    $terme = null,
        ?StatutColis $statut = null,
        ?string $ville = null
    ): array
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'client')
            ->addSelect('client')
            ->leftJoin('c.agenceDepot', 'agence')
            ->addSelect('agence')
            ->orderBy('c.createdAt', 'DESC');

            // Recherche textuelle
            if($terme !== null && trim($terme) !== '') {
                $terme = trim($terme);

                $queryBuilder
                    ->andWhere(
                        $queryBuilder->expr()->orX(
                            'c.numeroSuivi LIKE :terme',
                            'c.destinataire LIKE :terme',
                            'c.nom LIKE :terme',
                        )
                    )
                    ->setParameter('terme', '%'.$terme.'%');
            }

            // Le paramètre est désormais un objet
            if($statut !== null) {
                $queryBuilder
                    ->andWhere('c.statut = :statut')
                    ->setParameter('statut', $statut);
            }

            if($ville !== null && trim($ville) !== '') {
                $ville = trim($ville);

                $queryBuilder
                    ->andWhere('c.villeLivraison LIKE :ville')
                    ->setParameter('ville', '%'. $ville .'%');
            }

            return $queryBuilder
                ->getQuery()
                ->getResult();
    }   

    // Permet de compter le nombre de colis pour chaque statut
    /**
     * [
     *  [
     *      'statut' => 'EN_TRANSIT',
     *      'total' => 5
     *  ]
     * ]
    */

    public function compterParStatut(): array 
    {
        return $this->createQueryBuilder('c')
            ->select(
                'c.statut AS statut',
                'COUNT(c.id) AS total'
            )
            ->groupBy('c.statut')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    // Retourner les derniers colis enregistrés
    public function dernierColis(int $limit = 8): array 
    {
        return $this->createQueryBuilder('c')
        ->leftJoin('c.client', 'client')
        ->addSelect('client')
        ->leftJoin('c.agenceDepot', 'agence')
        ->addSelect('agence')
        ->orderBy('c.createdAt', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }

    // Permer de retourner les colis actuellement en incident et créés depuis au moins le nombre de jours demandés
    public function colisEnIncidentDepuis(int $jours): array 
    {
        $jours = max(0, $jours);

        $dateLimite = new \DateTimeImmutable(
            sprintf('-%d days', $jours)
        );

        return $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'client')
            ->addSelect('client')
            ->leftJoin('c.agenceDepot', 'agence')
            ->addSelect('agence')
            ->andWhere('c.statut = :statut')
            ->andWhere('c.createdAt <= :dateLimite')
            ->setParameter('statut', StatutColis::INCIDENT)
            ->setParameter('dateLimite', $dateLimite)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // On regroupe les informatiosn nécessaire au tableau de bord
    public function rechercherPourTableauDeBord(): array 
    {
        return [
            'parStatut' => $this->compterParStatut(),
            'incidents' => $this->colisEnIncidentDepuis(1),
            'derniers' => $this->dernierColis()
        ];
    }





//    /**
//     * @return Colis[] Returns an array of Colis objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Colis
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
