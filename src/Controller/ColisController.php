<?php

namespace App\Controller;

use App\Entity\Colis;
use App\Entity\MouvementColis;
use App\Enum\StatutColis;
use App\Form\ColisType;
use App\Form\MouvementColisType;
use App\Repository\ColisRepository;
use App\Service\TrackingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/colis')]
final class ColisController extends AbstractController
{
    #[Route('', name: 'app_colis_index', methods: ['GET'])]
    public function index(Request $request, ColisRepository $colisRepository): Response
    {
        $recherche = $request->query->getString('q');
        $statutValeur = $request->query->getString('statut'); // valeur brute
        $ville = $request->query->getString('ville');

        $statut = $statutValeur !== '' ? StatutColis::tryFrom($statutValeur) : null;

        return $this->render('colis/index.html.twig', [
            'items' => $colisRepository->rechercher(
                $recherche !== '' ? $recherche : null,
                $statut, // Exemple : StatutColis::EN_TRANSIT
                $ville !== '' ? $ville : null
            ),
            'statuts' => StatutColis::cases(),
            'filtres' => [
                'q' => $recherche,
                'statut' => $statut,
                //'statut' => $statutValeur,
                'ville' => $ville
            ]
        ]);
    }

    #[Route('/new', name: 'app_colis_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Création d'une nouvelle agence
        $colis = new Colis();
        $form = $this->createForm(ColisType::class, $colis);
        // Gestion de la requête
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($colis);
            $em->flush();
            $this->addFlash('success',
                sprintf(
                    'Le colis %s a été créé avec succès.',
                    $colis->getNumeroSuivi()
                ) 
            );
            return $this->redirectToRoute('app_colis_index');
        }
        
        return $this->render('colis/form.html.twig', [
            'form' => $form,
            'colis' => $colis,
            'page_title' => 'Créer un colis'
        ]);
    }

    #[Route('/{id}', name: 'app_colis_show', methods: ['GET', 'POST'])]
    public function show(Colis $colis, Request $request, TrackingService $trackingService): Response
    { 
        // Création d'un nouveau mouvement pour le colis
        $mouvement = (new MouvementColis())
            ->setColis($colis);

        $form = $this->createForm(MouvementColisType::class, $mouvement);

        $form->handleRequest($request);

        // On gère le changement de statut
        if($form->isSubmitted() && $form->isValid()) {
            $trackingService->changerStatut(
                $colis,
                $mouvement->getStatut(),
                $mouvement->getLieu(),
                $mouvement->getCommentaire()
            );

            $this->addFlash(
                'success',
                sprintf(
                    'Le colis %s possède maintenant le statut << %s >>.',
                    $colis->getNumeroSuivi(),
                    $mouvement->getStatut()->label()
                )
            );

            return $this->redirectToRoute('app_colis_show', [
                'id' => $colis->getId()
            ]);

        }

        return $this->render('colis/show.html.twig', [
            'colis' => $colis,
            'form' => $form
        ]);
    }

}
