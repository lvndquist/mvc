<?php

namespace App\Card;
use App\Card\Card;

class CardHand
{
    private array $cards;

    public function __construct()
    {
        $this->cards = [];
    }

    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
    }

    public function getCards(): array
    {
        return $this->cards;
    }

    public function removeCard(Card $card): bool
    {
        $color = $card->getColor();
        $value = $card->getValue();
        foreach ($this->cards as $index => $currentCard) {
            if($currentCard->getColor() === $color && $currentCard->getValue() === $value) {
                unset($cards[$index]);
                break;
            }
        }
        $this->cards = array_values($this->cards);
    }

}
