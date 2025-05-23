<?php

namespace App\Proj;

use App\Proj\Card;

/**
 * Class representing a hand of cards.
 */
class Hand
{
    /**
     * Array of cards representing a hand of cards.
     * @var Card[]
     */
    private array $cards;

    /**
     * Initiate the card hand object.
     */
    public function __construct()
    {
        $this->cards = [];
    }

    /**
     * Add a card to the hand.
     */
    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
    }

    /**
     * Get the cards in the hand.
     * @return array<Card>
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * Remove a card from the hand.
     */
    public function removeCard(Card $card): bool
    {
        $color = $card->getColor();
        $value = $card->getValue();
        $status = false;
        foreach ($this->cards as $index => $currentCard) {
            if ($currentCard->getColor() === $color && $currentCard->getValue() === $value) {
                unset($this->cards[$index]);
                $status = true;
                break;
            }
        }
        $this->cards = array_values($this->cards);
        return $status;
    }

    /**
     * Get string representation of hand.
     * @return string[] array of strings for each card in the hand
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
