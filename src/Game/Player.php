<?php

namespace App\Game;

use App\Card\CardHand;
use App\Card\Card;

use Exception;

class Player
{
    /** @var CardHand */
    private CardHand $hand;

    private int $score;

    public function __construct()
    {
        $this->hand = new CardHand();
        $this->score = 0;
    }

    public function getHand(): array
    {
        return $this->hand->getCards();
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function addCard($card): void
    {
        $this->hand->addCard($card);
        $this->score += $card->getValue();
    }

}
