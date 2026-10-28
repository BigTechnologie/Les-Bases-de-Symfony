<?php

namespace App\DataFixtures;

use App\Entity\Agence;
use App\Entity\Client;
use App\Entity\Colis;
use App\Entity\MouvementColis;
use App\Enum\StatutColis;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $villes = [
            ['Paris Louvre', '75001', 'Paris', '12 rue du Louvre'],
            ['Lyon Bellecour', '69002', 'Lyon', '4 place Bellecour'],
            ['Marseille Colbert', '13001', 'Marseille', '80rue Colbert'],
        ];

        $agences = [];

        foreach ($villes as $donnees) {
            $agence = (new Agence())
                ->setNom($donnees[0])
                ->setCodePostal($donnees[1])
                ->setVille($donnees[2])
                ->setAdresse($donnees[3]);

            $manager->persist($agence);
            $agences[] = $agence;
        }

        // 2. Création des clients
        $clients = [];

        $noms = [
            'Thibaud',
            'Thoma',
            'Marlène',
            'Florian',
            'Kandia',
            'Jérôme'
        ];

        foreach($noms as $i => $nom) {
            // $i = 5 et 3 agences => $i % count($agences) = 5 % 3 = 2
            $agence = $agences[$i % count($agences)]; 

            // Création d'un nouveau client
            $client = (new Client())
                ->setNom($nom)
                ->setEmail(strtolower($nom) . '@exemple.test')
                ->setTelephone('060000000' . $i)
                ->setAdresse(($i + 1) . ' rue Formation') 
                ->setCodePostal($agence->getCodePostal())
                ->setVille($agence->getVille())
                ->setAgenceReference($agence);
            
            $manager->persist($client);
            $clients[] = $client;
        }

        // 3. Récupération de toutes les cases de l'enum
        $statuts = StatutColis::cases();

        // 4. Créaton des colis et de leurs mouvements
        for($i = 1; $i <= 18; $i++) {
            $statut = $statuts[$i % count($statuts)]; // Statut du colis
            $client = $clients[$i % count($clients)];
            $agenceDepot = $agences[$i % count($agences)];
            $agenceDestination = $agences[($i + 1) % count($agences)];

            // Création d'un nouveau colis
            $colis = (new Colis())
                ->setNumeroSuivi('LP' . date('Y') . '000' . $i) // LP2025000$i
                ->setClient($client)
                ->setAgenceDepot($agenceDepot)
                ->setDestinataire('Destinaire ' . $i)
                ->setAdresseLivraison($i . ' avenue du courrier')
                ->setCodePostalLivraison($agenceDestination->getCodePostal())
                ->setVilleLivraison($agenceDestination->getVille())
                ->setPoidsKg(rand(1, 8) + 0.5)
                ->setStatut($statut)
                ->setCreatedAt(new \DateTimeImmutable('-' . $i . ' days'));

            // Premier mouvement du colis
            $mouvementCreation = (new MouvementColis())
                ->setStatut($statut)
                ->setLieu($colis->getVilleLivraison())
                ->setCommentaire('Création et envoie du colis');

            $colis->addMouvement($mouvementCreation);

            // Deuxième mouvement si le statut du colis est different de CREE
            if($statut !== StatutColis::CREE) {
                $mouvement = (new MouvementColis())
                    ->setStatut($statut)
                    ->setLieu($colis->getVilleLivraison())
                    ->setCommentaire('Mise à jour automatique');

                $colis->addMouvement($mouvement);
            }

            $manager->persist($colis);

        }


        $manager->flush();
    }
}
