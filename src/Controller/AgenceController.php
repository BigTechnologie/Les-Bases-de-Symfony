<?php

namespace App\Controller;

use App\Repository\AgenceRepository;
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
}
