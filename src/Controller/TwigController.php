<?php

namespace App\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TwigController extends AbstractController
{
    #[Route('/twig', name: 'app_index')]
    public function index(Request $request): Response
    {
        //return new Response('Bonjour '. $request->query->get('name'). 'Vous avez ' . $request->query->get('age'). ' ans');

        $date = new DateTime();
        //dd($date);
        $html = "<b>Hello Dawan</b>";

        return $this->render('twig/index.html.twig', [
            'name' => 'Jérôme',
            'date' => $date,
            'html' => $html
        ]);
    }
}
