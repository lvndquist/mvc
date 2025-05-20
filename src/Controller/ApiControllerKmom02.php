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
        /** @var DeckOfCards|null $deck */
        $deck = $session->get("deck");

        if (!$deck) {
            $this->setSession($session);
            $deck = $session->get("deck");
        }

        if ($deck instanceof DeckOfCards) {

            $sortedDeck = $deck->sort();
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
        return new JsonResponse(Response::HTTP_BAD_REQUEST);
    }


    #[Route("/api/deck/shuffle", name: "post_deck_shuffle", methods: ["POST"])]
    public function shuffle(SessionInterface $session): Response
    {
        $this->setSession($session);
        /** @var DeckOfCards $deck */
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

        /** @var DeckOfCards|null $deck */
        $deck = $session->get("deck");
        /** @var CardHand[]|null $hands*/
        $hands = $session->get("hands");
        if (!$deck || !$hands) {
            $this->setSession($session);
            $deck = $session->get("deck");
            $hands = $session->get("hands");
        }


        if ($deck instanceof DeckOfCards
            && is_array($hands)
            && $hands[0] instanceof CardHand
        ) {
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
        return new JsonResponse(Response::HTTP_BAD_REQUEST);
    }

    #[Route("/api/deck/draw/", name: "post_deck_draw_number", methods: ["POST"])]
    public function drawNumber(SessionInterface $session, Request $request): Response
    {
        $number = $request->request->get('num_cards');

        /** @var DeckOfCards|null $deck */
        $deck = $session->get("deck");
        /** @var CardHand[]|null $hands*/
        $hands = $session->get("hands");

        if (!$deck || !$hands) {
            $this->setSession($session);
            $deck = $session->get("deck");
            $hands = $session->get("hands");
        }

        if ($deck instanceof DeckOfCards
            && is_array($hands)
            && $hands[0] instanceof CardHand
        ) {
            $deck->drawMultiple($number, $hands[0]);
            $isEmpty = $deck->isEmpty();

            $cards = $hands[0]->toString();

            $response = new JsonResponse(["size" => $deck->size(), "hand" => $cards]);
            $response->setEncodingOptions(
                $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
            $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

            $session->set("deck", $deck);
            $session->set("hands", $hands);

            return $response;
        }
        return new JsonResponse(Response::HTTP_BAD_REQUEST);
    }

    #[Route("/api/deck/deal/{players<\d+>}/{cards<\d+>}", name: "post_deck_deal_players_cards", methods:["POST"])]
    public function deal(int $players, int $cards): Response
    {
        $deck = new DeckOfCards(false);
        $deck->shuffle();
        $playerHands = [];
        for ($j = 0; $j < $players; $j++) {
            $hand = new CardHand();
            $deck->drawMultiple($cards, $hand);
            $playerHands[$j] = $hand->toString();
        }
        $response = new JsonResponse(["size" => $deck->size(), "players" => $playerHands]);
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
