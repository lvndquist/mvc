<?php

namespace App\Proj;

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
     * Player's name.
     */
    private string $name;

    /**
     * Player's money.
     */
    private int $money;

    /**
     * Player's current bet.
     */
    private int $currentBet;

    /**
     * Player folded.
     */
    private bool $folded;

    /**
     * Player all in.
     */
    private bool $allIn;

    /**
     * Initialize the player object.
     */
    public function __construct(string $name, int $money)
    {
        $this->hand = new CardHand();
        $this->name = $name;
        $this->money = $money;
        $this->currentBet = 0;
        $this->folded = false;
        $this->allIn = false;
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
     * Gets the name of the player.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the money of the player.
     */
    public function getMoney(): int
    {
        return $this->money;
    }

    /**
     * Gets the current bet of the player.
     */
    public function getCurrentBet(): int
    {
        return $this->currentBet;
    }

    /**
     * Has player folded?
     */
    public function isFolded(): bool
    {
        return $this->isFolded;
    }

    /**
     * Has player went all in?
     */
    public function isAllIn(): bool
    {
        return $this->isAllIn;
    }

    /**
     * Adds a card to the player's hand.
     */
    public function addCard(Card $card): void
    {
        $this->hand->addCard($card);
    }

    /**
     * Set money of player.
     */
    public function setMoney(int $money)
    {
        $this->money = $money;
    }

    /**
     * Set current bet of player.
     */
    public function setCurrentBet(int $bet)
    {
        $this->currentBet = $bet;
    }

    /**
     * Set folded flag of player.
     */
    public function setFolded(bool $val)
    {
        $this->isFolded = $val;
    }

    /**
     * Set all in flag of player.
     */
    public function setAllIn(bool $val)
    {
        $this->isAllIn = $val;
    }

    /**
     * Make a bet.
     */
    public function makeBet(int $amount)
    {
        if ($amount >= $this->money) {
            $this->allIn = true;
            $amount = $this->money;
        }
        $this->money -= $amount;
        $this->currentBet += $amount;
    }
}
