<?php

namespace App\Service;

use App\Entity\Colis;
use App\Entity\MouvementColis;
use App\Enum\StatutColis;
use Doctrine\ORM\EntityManagerInterface;

class TrackingService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function changerStatut(
        Colis $colis, 
        StatutColis $statut, 
        string $lieu, 
        ?string $commentaire = null 
    ): void {
        $colis->setStatut($statut);

        $mouvement = (new MouvementColis())
            ->setColis($colis) 
            ->setStatut($statut) 
            ->setLieu($lieu) 
            ->setCommentaire($commentaire); 

        $this->entityManager->persist($mouvement);
        $this->entityManager->flush();
    }
}
