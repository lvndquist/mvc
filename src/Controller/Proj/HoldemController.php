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
        $game->updateGameState();
        $players = $game->getPlayers();
        $data = [
            "players" => $players,
            "currPlayerIndex" => $game->getCurrPlayerIndex(),
            "pot" => $game->getPot(),
            "canCheck" => $game->canCheck(),
            "dealerCards" => $game->getDealerCards()
        ];
        return $this->render('proj/game.html.twig', $data);
    }

    #[Route('/proj/user-input', name: 'user_input', methods: ['POST'])]
    public function userInput(Request $request, SessionInterface $session): Response
    {
        /** @var Game|null $game */
        $game = $session->get("game");

        if ($request->request->has("fold")) {
            $game->playerFold(0);
        } elseif ($request->request->has("call")) {
            $game->playerCall(0);
        } elseif ($request->request->has("raise")) {
            $amount = $request->request->getInt("money");
            $game->playerRaise(0, $amount);
        } elseif ($request->request->has("check")) {
            $game->playerCheck(0);
        }
        $session->set("game", $game);
        return $this->redirectToRoute('holdem');
    }
}
