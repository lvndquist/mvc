<?php

namespace App\Proj;

use App\Proj\CardGraphic;
use App\Proj\CardHand;
use App\Proj\Deck;
use App\Proj\Player;
use Exception;

/**
 * Class representing a deck of cards.
 */
class Game
{
    private array $players;
    private array $dealerCards;
    private Deck $deck;
    private int $pot;
    private int $currPlayerIndex;
    private int $phase;
    private int $currentBet;
    private int $numPlayers = 4;

    public function __construct(int $startingMoney, string $playerName)
    {
        $this->deck = new Deck(true);
        $this->players = [];
        for ($i = 0; $i < $this->numPlayers; $i++) {
            $name = "Player " . $i + 1;
            if ($i == 0) {
                $name = $playerName;
            }
            $this->players[] = new Player($name, $startingMoney);
        }
        $this->pot = 0;
        $this->currPlayerIndex = 0;
        $this->phase = 0;
        $this->currentBet = 0;
        $this->dealToPlayers();
    }

    public function dealToPlayers()
    {
        for ($j = 0; $j < 2; $j++) {
            for ($i = 0; $i < $this->numPlayers; $i++) {
                $this->players[$i]->addCard($this->deck->draw());
            }
        }
    }

    public function dealerDraw()
    {

    }

    /**
     * Get players.
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * Get player cards.
     */
    public function getPlayerCards($playerIndex)
    {
        return $this->players[$playerIndex]->getHand();
    }

    /**
     * Get dealer cards.
     */
    public function getDealerCards()
    {
        return $this->dealerCards;
    }

    /**
     * Get deck.
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * Get pot.
     */
    public function getPot()
    {
        return $this->pot;
    }

    /**
     * Get current player index.
     */
    public function getCurrPlayerIndex()
    {
        return $this->currPlayerIndex;
    }

    /**
     * Get phase.
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * Get current bet.
     */
    public function getCurrentBet()
    {
        return $this->currentBet;
    }

    /**
     * Get number of players.
     */
    public function getNumPlayers()
    {
        return $this->numPlayers;
    }
}
