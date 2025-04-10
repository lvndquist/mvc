<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiControllerKmom02 extends AbstractController
{

    #[Route("/api/deck", name: "get_deck")]
    public function deck(): Response
    {
        return $this->render('report/api.html.twig');
    }

    #[Route("/api/deck/shuffle", name: "post_deck_shuffle", methods: ["POST"])]
    public function shuffle(): Response
    {
        return $this->render('report/api.html.twig');
    }

    #[Route("/api/deck/draw", name: "post_deck_draw", methods: ["POST"])]
    public function draw(): Response
    {
        return $this->render('report/api.html.twig');
    }

    #[Route("/api/deck/draw/{number<\d+>}", name: "post_deck_draw_number", methods: ["POST"])]
    public function drawNumber(): Response
    {
        return $this->render('report/api.html.twig');
    }

    #[Route("/api/deck/deal/{players<\d+>}/{cards<\d+>}", name: "post_deck_deal_players_cards", methods:["POST"])]
    public function api(): Response
    {
        return $this->render('report/api.html.twig');
    }

}
