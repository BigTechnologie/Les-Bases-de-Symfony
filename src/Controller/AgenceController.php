<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Form\AgenceType;
use App\Repository\AgenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agence')]
final class AgenceController extends AbstractController
{
    #[Route('', name: 'app_agence_index')]
    public function index(Request $request, AgenceRepository $repo): Response
    {
        //dd($request);
        $items = method_exists($repo, 'rechercher') ? $repo->rechercher($request->query->get('q')) : $repo->findBy([], ['id' => 'DESC']);
        //dd($items);
        return $this->render('agence/index.html.twig', [
            'items' => $items,
            'q' => $request->query->get('q')
        ]);
    }

    #[Route('/new', name: 'app_agence_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Création d'une nouvelle agence
        $item = new Agence();

        $form = $this->createForm(AgenceType::class, $item);

        // Gestion de la requête
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->addFlash('success', 'Enregistrement créé.');
            return $this->redirectToRoute('app_agence_index');
        }
        
        return $this->render('agence/form.html.twig', [
            'form' => $form,
            'item' => $item
        ]);
    }

    #[Route('/{id}', name: 'app_agence_show', requirements: ['id' => '\\d+'])]
    public function show(Agence $item): Response
    {  
        return $this->render('agence/show.html.twig', [
            'item' => $item
        ]);
    }

    #[Route('/{id}/edit', name: 'app_agence_edit')]
    public function edit(Agence $item, Request $request, EntityManagerInterface $em): Response
    {  
        $form = $this->createForm(AgenceType::class, $item);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Modification enregistrée.');
            return $this->redirectToRoute('app_agence_index');
        }

        return $this->render('agence/form.html.twig', [
            'item' => $item,
            'form' => $form
        ]);
    }

    #[Route('/{id}/delete', name: 'app_agence_delete', methods: ['POST'])]
    public function delete(Agence $item, Request $request, EntityManagerInterface $em): Response
    {  
        if($this->isCsrfTokenValid('delete' . $item->getId(), $request->request->get('_token'))) {
            $em->remove($item);
            $em->flush();
            $this->addFlash('success', 'Suppression éffectuée.');
        }
        return $this->redirectToRoute('app_agence_index');
    }


}
