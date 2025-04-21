<?php

namespace App\Card;

use App\Card\CardGraphic;
use Exception;

class DeckOfCards
{
    /** @var Card[] */
    private array $cards;

    /**
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

    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    /** @return array<Card> */
    public function getCards(): array
    {
        return $this->cards;
    }

    public function draw(): Card
    {
        if ($this->isEmpty()) {
            throw new Exception("Empty deck!");
        }
        /** @var Card $card */
        $card = array_pop($this->cards);
        return $card;
    }

    public function isEmpty(): bool
    {
        if ($this->size() === 0) {
            return true;
        }
        return false;
    }

    public function size(): int
    {
        return count($this->cards);
    }

}
