<?php

namespace App\Repository;

use App\Entity\Colis;
use App\Enum\StatutColis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ColisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Colis::class);
    }

    /**
     * Recherche des colis à partir de plusieurs critères facultatifs.
     */
    public function rechercher(
        ?string $terme = null,
        ?StatutColis $statut = null,
        ?string $ville = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'client')
            ->addSelect('client')
            ->leftJoin('c.agenceDepot', 'agence')
            ->addSelect('agence')
            ->orderBy('c.createdAt', 'DESC');

        /*
         * Recherche textuelle dans :
         *
         * - le numéro de suivi ;
         * - le nom du destinataire ;
         * - le nom du client.
         */
        if ($terme !== null && trim($terme) !== '') {
            $terme = trim($terme);

            $queryBuilder
                ->andWhere( 
                    $queryBuilder->expr()->orX( 
                        'c.numeroSuivi LIKE :terme', 
                        'c.destinataire LIKE :terme', 
                        'client.nom LIKE :terme' 
                    )
                )
                ->setParameter('terme', '%'.$terme.'%');
        }

        /*
         * Le paramètre est désormais un objet StatutColis.
         *
         * Exemple :
         *
         * StatutColis::EN_TRANSIT
         */
        if ($statut !== null) { 
            $queryBuilder
                ->andWhere('c.statut = :statut') 
                ->setParameter('statut', $statut);
        }

        if ($ville !== null && trim($ville) !== '') { 
            $ville = trim($ville);

            $queryBuilder
                ->andWhere('c.villeLivraison LIKE :ville')
                ->setParameter('ville', '%'.$ville.'%');
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de colis pour chaque statut.
     *
     * Le résultat ressemble à :
     *
     * [
     *     [
     *         'statut' => 'EN_TRANSIT',
     *         'total' => 5,
     *     ],
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

    /**
     * Retourne les derniers colis enregistrés.
     */
    public function derniersColis(int $limit = 8): array
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

    /**
     * Retourne les colis actuellement en incident et créés
     */
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

    /**
     * Regroupe les informations nécessaires au tableau de bord.
     */
    public function rechercherPourTableauDeBord(): array
    {
        return [
            'parStatut' => $this->compterParStatut(),
            'incidents' => $this->colisEnIncidentDepuis(1),
            'derniers' => $this->derniersColis(),
        ];
    }
}