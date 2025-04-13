<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Card\DeckOfCards;
use App\Card\CardHand;

class ApiControllerKmom02 extends AbstractController
{
    #[Route("/api/deck", name: "get_deck")]
    public function deck(SessionInterface $session): Response
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
            $sortedDeck[] = $card[0]->toString();
        }
        $response = new JsonResponse(["deck" => $sortedDeck]);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        return $response;
    }


    #[Route("/api/deck/shuffle", name: "post_deck_shuffle", methods: ["POST"])]
    public function shuffle(SessionInterface $session): Response
    {
        $this->setSession($session);
        $deck = $session->get("deck");
        $cards = [];
        foreach ($deck->getCards() as $card) {
            $cards[] = $card->toString();
        }
        $response = new JsonResponse(["deck" => $cards]);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    #[Route("/api/deck/draw", name: "post_deck_draw", methods: ["POST"])]
    public function draw(SessionInterface $session): Response
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

        $cards = [];
        foreach ($hands[0]->getCards() as $card) {
            $cards[] = $card->toString();
        }

        $response = new JsonResponse(["size" => $deck->size(), "hand" => $cards]);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        $session->set("deck", $deck);
        $session->set("hands", $hands);

        return $response;
    }

    #[Route("/api/deck/draw/", name: "post_deck_draw_number", methods: ["POST"])]
    public function drawNumber(SessionInterface $session, Request $request): Response
    {
        $number = $request->request->get('num_cards');

        $deck = $session->get("deck");
        $hands = $session->get("hands");
        if (!$deck || !$hands) {
            $this->setSession($session);
            $deck = $session->get("deck");
            $hands = $session->get("hands");
        }
        $isEmpty = $deck->isEmpty();
        for ($i = 0; $i < $number; $i++) {
            if (!$isEmpty) {
                $card = $deck->draw();
                $hands[0] -> addCard($card);
                $isEmpty = $deck->isEmpty();
            }
        }

        $cards = [];
        foreach ($hands[0]->getCards() as $card) {
            $cards[] = $card->toString();
        }

        $response = new JsonResponse(["size" => $deck->size(), "hand" => $cards]);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        $session->set("deck", $deck);
        $session->set("hands", $hands);

        return $response;
    }

    #[Route("/api/deck/deal/{players<\d+>}/{cards<\d+>}", name: "post_deck_deal_players_cards", methods:["POST"])]
    public function deal(SessionInterface $session, $players, $cards): Response
    {
        $deck = new DeckOfCards(false);
        $deck->shuffle();
        $hands = [];
        $player_hands = [];
        for ($i = 0; $i < $players; $i++) {
            $hands[] = new CardHand();
        }
        $isEmpty = false;
        for ($j = 0; $j < $players; $j++) {
            for ($i = 0; $i < $cards; $i++) {
                if (!$isEmpty) {
                    $card = $deck->draw();
                    $hands[$j] -> addCard($card);
                    $player_hands[$j][$i] = $card->toString();
                    $isEmpty = $deck->isEmpty();
                }
            }
        }

        $response = new JsonResponse(["size" => $deck->size(), "players" => $player_hands]);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        return $response;
    }

    public function setSession(SessionInterface $session): void
    {
        $deck = new DeckOfCards(false);
        $hands = [new CardHand()];
        $deck->shuffle();
        $session->set("deck", $deck);
        $session->set("hands", $hands);
    }

}
