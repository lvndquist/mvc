<?php

namespace App\Controller\Game;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Card\DeckOfCards;
use App\Game\GameState;

class GameController extends AbstractController
{
    #[Route("/game", name: "game")]
    public function game(): Response
    {
        return $this->render('game/game.html.twig');
    }

    #[Route("/game/doc", name: "doc")]
    public function doc(): Response
    {
        return $this->render('game/doc.html.twig');
    }

    #[Route("/game/init", name: "init")]
    public function init(SessionInterface $session): Response
    {
        $session->clear();
        $gameState = new GameState();
        $session->set("gameState", $gameState);
        return $this->redirectToRoute('board');
    }

    #[Route("/game/board", name: "board")]
    public function board(SessionInterface $session): Response
    {
        $gameState = $session->get("gameState");
        $gameIsOver = $gameState->gameIsOver();
        $winner = $gameState->getWinner();
        $deck = $gameState->getDeck();
        $bank = $gameState->getBank();
        $player = $gameState->getPlayer();
        $data = [
            "gameState" => $gameState,
            "gameIsOver" => $gameIsOver,
            "winner" => $winner,
            "deck" => $deck,
            "bank" => $bank,
            "player" => $player
        ];

        return $this->render('game/board.html.twig', $data);
    }

    #[Route("/game/draw", name: "draw")]
    public function draw(SessionInterface $session): Response
    {
        $gameState = $session->get("gameState");
        $gameState->playerDraw();
        $session->set("gameState", $gameState);
        return $this->redirectToRoute('board');
    }

    #[Route("/game/stop", name: "stop")]
    public function stop(): Response
    {
        return $this->redirectToRoute('board');
    }

}
