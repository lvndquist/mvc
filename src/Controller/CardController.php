<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Card\DeckOfCards;
use App\Card\CardHand;

class CardController extends AbstractController
{
    #[Route("/card", name: "card")]
    public function card(): Response
    {
        return $this->render('card/card.html.twig');
    }

    #[Route("/card/deck", name: "card_deck")]
    public function cardDeck(SessionInterface $session): Response
    {
        $deck = $session->get("deck");

        if (!$deck) {
            $this->setSession($session);
            $deck = $session->get("deck");
        }
        $cards = [];
        foreach ($deck->getCards() as $card) {
            $color = $card->getColor();
            $value = $card->getValue();
            $cards[] = [$card, $color, $value];
        }
        usort($cards, function ($i, $j) {
            if ($i[1] === $j[1]) {
                return $i[2] <=> $j[2];
            }
            return $i[1] <=> $j[1];
        });
        $sortedDeck = [];
        foreach ($cards as $card) {
            $sortedDeck[] = $card[0];
        }

        $data = [
            "deck" => $sortedDeck
        ];
        return $this->render('card/deck.html.twig', $data);
    }

    #[Route("/card/deck/shuffle", name: "deck_shuffle")]
    public function deckShuffle(SessionInterface $session): Response
    {
        $this->setSession($session);
        $deck = $session->get("deck");
        $data = [
            "deck" => $deck->getCards()
        ];
        return $this->render('card/deck.html.twig', $data);
    }

    #[Route("/card/deck/draw", name: "deck_draw")]
    public function deckDraw(SessionInterface $session): Response
    {
        $deck = $session->get("deck");
        $hands = $session->get("hands");
        if (!$deck || !$hands) {
            $this->setSession($session);
            $deck = $session->get("deck");
            $hands = $session->get("hands");
        }
        $isEmpty = $deck->isEmpty();
        if (!$isEmpty) {
            $card = $deck->draw();
            $hands[0] -> addCard($card);
            $isEmpty = $deck->isEmpty();
        }

        $data = [
            "hands" => $hands[0]->getCards(),
            "size" => $deck->size(),
            "isEmpty" => $isEmpty
        ];

        $session->set("deck", $deck);
        $session->set("hands", $hands);
        return $this->render('card/draw.html.twig', $data);
    }

    #[Route("/card/deck/draw/{number<\d+>}", name: "deck_draw_number")]
    public function deckDrawNumber(int $number, SessionInterface $session): Response
    {
        $deck = $session->get("deck");
        $hands = $session->get("hands");
        if (!$deck || !$hands) {
            $this->setSession($session);
            $deck = $session->get("deck");
            $hands = $session->get("hands");
        }
        $isEmpty = $deck->isEmpty();
        if (!$isEmpty) {
            for ($i = 0; $i < $number; $i++) {
                if (!$isEmpty) {
                    $card = $deck->draw();
                    $hands[0] -> addCard($card);
                    $isEmpty = $deck->isEmpty();
                }
            }
        }

        $data = [
            "hands" => $hands[0]->getCards(),
            "size" => $deck->size(),
            "isEmpty" => $isEmpty
        ];

        $session->set("deck", $deck);
        $session->set("hands", $hands);
        return $this->render('card/draw.html.twig', $data);
    }

    #[Route("/card/deck/deal/{players<\d+>}/{cards<\d+>}", name: "deck_deal_players_cards")]
    public function dealPlayersCards(int $players, int $cards, SessionInterface $session): Response
    {
        $deck = new DeckOfCards(true);
        $deck->shuffle();
        $hands = [];
        for ($i = 0; $i < $players; $i++) {
            $hands[] = new CardHand();
        }
        $isEmpty = false;
        for ($j = 0; $j < $players; $j++) {
            for ($i = 0; $i < $cards; $i++) {
                if (!$isEmpty) {
                    $card = $deck->draw();
                    $hands[$j] -> addCard($card);
                    $isEmpty = $deck->isEmpty();
                }
            }
        }

        $data = [
            "hands" => $hands,
            "size" => $deck->size(),
            "isEmpty" => $isEmpty
        ];

        $session->set("deck", $deck);
        $session->set("hands", $hands);
        return $this->render('card/deal.html.twig', $data);
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
