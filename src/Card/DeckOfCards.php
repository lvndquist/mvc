<?php

namespace App\Card;
use App\Card\CardGraphic;

class DeckOfCards
{
    private array $cards;

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

    public function getCards(): array
    {
        return $this->cards;
    }

    public function draw(): Card
    {
        /*
        if ($this->isEmpty()) {
            throw new \Exception("Empty deck!");
        }*/
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
        return count($this->cards);
    }

}
