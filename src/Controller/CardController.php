<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Card\DeckOfCards;

class CardController extends AbstractController
{
    #[Route("/session", name: "session", methods: ['GET'])]
    public function session(
        SessionInterface $session
    ): Response
    {

        $sessionData = $session -> all();
        $data = [
            "session" => $sessionData
        ];
        return $this->render('card/session.html.twig', $data);
    }

    #[Route("/session/delete", name: "session_delete", methods: ['GET'])]
    public function deleteSession(
        SessionInterface $session
    ): Response
    {
        $session->clear();

        $this->addFlash(
            'notice',
            'Session was deleted!'
        );

        return $this->redirectToRoute('session');
    }

    #[Route("/card", name: "card")]
    public function card(): Response
    {
        return $this->render('card/card.html.twig');
    }

    #[Route("/card/deck", name: "card_deck")]
    public function cardDeck(): Response
    {
        $deck = new DeckOfCards();

        $data = [
            "deck" => $deck->getCards()
        ];
        return $this->render('card/deck.html.twig', $data);
    }

    #[Route("/card/deck/shuffle", name: "deck_shuffle")]
    public function deckShuffle(): Response
    {
        $deck = new DeckOfCards();
        $deck->shuffle();
        $data = [
            "deck" => $deck->getCards()
        ];
        return $this->render('card/deck.html.twig', $data);
    }

    #[Route("/card/deck/draw", name: "deck_draw")]
    public function deckDraw(): Response
    {
        return $this->render('card/card.html.twig');
    }

}
