<?php

namespace App\Controller\Proj;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Proj\Game;

class HoldemController extends AbstractController
{
    #[Route("/proj", name: "proj")]
    public function index(): Response
    {
        return $this->render('proj/index.html.twig');
    }

    #[Route("/proj/about", name: "about")]
    public function about(): Response
    {
        return $this->render('proj/index.html.twig');
    }

    #[Route("/proj/start", name: "start_game", methods: ["POST"])]
    public function start(SessionInterface $session, Request $request): Response
    {
        $name = (string) $request->request->get('name');
        $startingMoney = (int) $request->request->get('money');
        $game = new Game($startingMoney, $name);
        $session->set("game", $game);
        return $this->redirectToRoute('holdem');
    }

    #[Route("/proj/game", name: "holdem")]
    public function holdem(SessionInterface $session): Response
    {
        /** @var Game|null $game */
        $game = $session->get("game");
        $data = [
            "players" => $game->getPlayers()
        ];
        return $this->render('proj/game.html.twig', $data);
    }

}
