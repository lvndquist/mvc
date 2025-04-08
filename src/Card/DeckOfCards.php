<?php

namespace App\Card;
use App\Card\Card;

class DeckOfCards
{
    private array $cards;

    public function __construct()
    {
        $colors = [0, 1, 2, 3];
        foreach ($colors as $color) {
            for ($val = 1; $val < 14; $val++) {
                $this->cards[] = new Card($val, $color);
            }
        }
    }

    public function getCards(): array
    {
        return $this->cards;
    }

    public function draw(): Card
    {
        return array_pop($this->cards);
    }

    public function isEmpty(): bool
    {
        if ($this->size($this->cards) === 0) {
            return true;
        }
        return false;
    }

    public function size(): int
    {
        return count($this-cards);
    }

}
