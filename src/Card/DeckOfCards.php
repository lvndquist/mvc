<?php

namespace App\Card;

use App\Card\CardGraphic;
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

}
