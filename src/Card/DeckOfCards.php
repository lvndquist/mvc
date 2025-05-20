<?php

namespace App\Card;

use App\Card\CardGraphic;
use App\Card\CardHand;
use Exception;

/**
 * Class representing a deck of cards.
 */
class DeckOfCards
{
    /**
     *  Array of cards representing the card deck.
     *  @var Card[]
     */
    private array $cards;

    /**
     * Initialize the card deck.
     * @SuppressWarnings("PHPMD.ElseExpression")
     */
    public function __construct(bool $graphic)
    {
        $colors = [0, 1, 2, 3];
        foreach ($colors as $color) {
            for ($val = 1; $val < 14; $val++) {
                if ($graphic) {
                    $this->cards[] = new CardGraphic($val, $color);
                } else {
                    $this->cards[] = new Card($val, $color);
                }
            }
        }
    }

    /**
     * Randomize the cards in the deck.
     */
    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    /**
     *  Get all cards in the deck.
     *  @return array<Card>
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * Draw a card from the deck.
     */
    public function draw(): Card
    {
        if ($this->isEmpty()) {
            throw new Exception("Empty deck!");
        }
        /** @var Card $card */
        $card = array_pop($this->cards);
        return $card;
    }

    /**
     * Check if the deck is empty or not.
     */
    public function isEmpty(): bool
    {
        if ($this->size() === 0) {
            return true;
        }
        return false;
    }

    /**
     * Get the size of the deck.
     */
    public function size(): int
    {
        return count($this->cards);
    }

    /**
     * Get the size of the deck.
     * @return array<Card>
     */
    public function sort(): array
    {
        $cards = [];
        foreach ($this->cards as $card) {
            $color = $card->getColor();
            $value = $card->getValue();
            $cards[] = [$card, $color, $value];
        }
        usort($cards, function ($card1, $card2) {
            if ($card1[1] === $card2[1]) {
                return $card1[2] <=> $card2[2];
            }
            return $card1[1] <=> $card2[1];
        });
        $sortedDeck = [];
        foreach ($cards as $card) {
            $sortedDeck[] = $card[0];
        }

        return $sortedDeck;
    }

    /**
     * Draw multiple number of cards from deck.
     */
    public function drawMultiple(int $number, CardHand $hand)
    {
        for ($i = 0; $i < $number && !$this->isEmpty(); $i++) {
            $card = $this->draw();
            $hand -> addCard($card);
        }
    }

    /**
     * Get string representation for deck.
     */
    public function toString(): array
    {
        $cards = [];
        foreach ($this->getCards() as $card) {
            $cards[] = $card->toString();
        }
        return $cards;
    }
}
