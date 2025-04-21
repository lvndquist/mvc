<?php

namespace App\Game;

use App\Card\CardHand;
use Exception;

class Player
{
    /** @var CardHand */
    private CardHand $cards;

    private int $score;

    public function __construct()
    {
        $this->cards= new CardHand();
        $this->score= 0;
    }

    public function getCards(): CardHand
    {
        return $this->cards;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function draw(): Card
    {
        $card = $this->deck->draw();
        $cardValue = $card->getValue();
        $this->score += $cardValue;
        $this->cards->addCard($card);
        return $card;
    }
}
