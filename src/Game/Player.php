<?php

namespace App\Game;

use App\Card\CardHand;
use App\Card\Card;
use Exception;

/**
 * Class representing a player.
 */
class Player
{
    /**
     * Player's hand containing their cards.
     * @var CardHand
     */
    private CardHand $hand;

    /**
     * Player's score.
     */
    private int $score;

    /**
     * Initialize the player object.
     */
    public function __construct()
    {
        $this->hand = new CardHand();
        $this->score = 0;
    }

    /**
     * Gets all cards in the player's hand.
     * @return array<Card>
     */
    public function getHand(): array
    {
        return $this->hand->getCards();
    }

    /**
     * Gets the score of the player.
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * Adds a card to the player's hand.
     */
    public function addCard(Card $card): void
    {
        $this->hand->addCard($card);
        $this->score += $card->getValue();
    }

    /**
     * Sets the score of the player.
     */
    public function setScore(int $scoreToSet): void
    {
        $this->score = $scoreToSet;
    }

}
