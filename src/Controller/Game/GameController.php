<?php

namespace App\Controller\Game;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Card\DeckOfCards;
use App\Card\CardHand;

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
    public function init(): Response
    {

        return $this->redirectToRoute('board');
    }

    #[Route("/game/board", name: "board")]
    public function board(): Response
    {
        return $this->render('game/board.html.twig');
    }

    public function setSession(SessionInterface $session): void
    {
        $deck = new DeckOfCards(true);
        $hands = [new CardHand()];
        $deck->shuffle();
        $session->set("deck", $deck);
        $session->set("hands", $hands);
    }
}
