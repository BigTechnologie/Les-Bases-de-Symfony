<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client')]
final class ClientController extends AbstractController
{
    #[Route('', name: 'app_client_index')]
    public function index(Request $request, ClientRepository $repo): Response
    {
        $items = method_exists($repo, 'rechercher') ? $repo->rechercher($request->query->get('q')) : $repo->findBy([], ['id' => 'DESC']);
        //dd($items);
        return $this->render('client/index.html.twig', [
            'items' => $items,
            'q' => $request->query->get('q')
        ]);
    }

    #[Route('/new', name: 'app_client_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Création d'une nouvelle agence
        $item = new Client();
        $form = $this->createForm(ClientType::class, $item);
        // Gestion de la requête
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->addFlash('success', 'Enregistrement créé.');
            return $this->redirectToRoute('app_client_index');
        }
        
        return $this->render('client/form.html.twig', [
            'form' => $form,
            'item' => $item
        ]);
    }
}
