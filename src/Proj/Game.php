<?php

namespace App\Proj;

use App\Proj\CardGraphic;
use App\Proj\Hand;
use App\Proj\Deck;
use App\Proj\Player;
use Exception;

/**
 * Class representing a deck of cards.
 */
class Game
{
    private array $players;
    private Hand $dealerCards;
    private Deck $deck;
    private int $pot;
    private int $currPlayerIndex;
    private int $phase;
    private int $currentBet;
    private int $winner;
    private int $numPlayers = 4;
    private int $smallBlind = 20;
    private int $bigBlind = 40;

    public function __construct(int $startingMoney, string $playerName)
    {
        $this->deck = new Deck(true);
        $this->deck->shuffle();
        $this->players = [];
        for ($i = 0; $i < $this->numPlayers; $i++) {
            $name = "Player " . $i + 1;
            if ($i == 0) {
                $name = $playerName;
            }
            $this->players[] = new Player($name, $startingMoney);
        }
        $this->pot = 0;
        $this->currPlayerIndex = 1;
        $this->phase = 0;
        $this->currentBet = 0;
        $this->winner = -1;

        $this->dealToPlayers();

        $this->players[3]->makeBet($this->smallBlind);
        $this->players[2]->makeBet($this->bigBlind);
        $this->pot += ($this->smallBlind + $this->bigBlind);
    }

    public function dealToPlayers()
    {
        for ($j = 0; $j < 2; $j++) {
            for ($i = 0; $i < $this->numPlayers; $i++) {
                $this->players[$i]->addCard($this->deck->draw());
            }
        }
    }

    /**
     * Update the game state.
     */
    public function updateGameState()
    {
        $player = $this->players[$this->currPlayerIndex];
        if ($this->allPlayed() || $this->onePlayerLeft()) {
            if ($this->onePlayerLeft()) {
                $this->winner = $this->currPlayerIndex;
                $this->handleWin();
                return;
            }
            $this->nextPhase();
            return;
        }
        $this->nextPlayer();
        return;
    }

    /**
     * Go to next phase.
     */
    public function nextPhase()
    {
        $this->phase++;
        foreach ($this->players as $player) {
            $player->setPlayed(false);
        }
    }

    /**
     * Update the currPlayerIndex, ignoring players who have folded.
     */
    public function nextPlayer(): void
    {
        $nextIndex = $this->currPlayerIndex;
        while(true) {
            $nextIndex = ($nextIndex - 1 + $this->numPlayers) % $this->numPlayers;
            if (!$this->players[$nextIndex]->isFolded()) {
                $this->currPlayerIndex = $nextIndex;
                break;
            }
        }
    }

    /**
     * Check if all players played.
     */
    public function allPlayed(): bool
    {
        foreach ($this->players as $player) {
            if(!$player->isFolded() && !$player->hasPlayed()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if there is only one player left.
     */
    public function onePlayerLeft(): bool
    {
        $count = 0;
        foreach ($this->players as $player) {
            if (!$player->isFolded()) {
                $count += 1;
            }
        }
        return ($count === 1);
    }

    public function dealerDraw()
    {
        return;
    }

    public function handleWin()
    {
        return;
    }

    /**
     * Player fold.
     */
    public function playerFold(int $playerIndex)
    {
        $player = $this->players[$playerIndex];
        $player->setFolded(true);
    }

    /**
     * Player call.
     */
    public function playerCall(int $playerIndex)
    {
        $player = $this->players[$playerIndex];
        $callAmount = $this->currentBet - $player->getCurrentBet();
        $player->makeBet($callAmount);
        $this->pot += $callAmount;
    }

    /**
     * Player raise.
     */
    public function playerRaise(int $playerIndex, int $amount)
    {
        $player = $this->players[$playerIndex];
        $total = $this->currentBet + $amount;
        $raiseAmount = $total - $player->getCurrentBet();

        $player->makeBet($raiseAmount);
        $this->pot += $raiseAmount;
    }

    /**
     * Player check.
     */
    public function playerCheck(int $playerIndex)
    {
        $player = $this->players[$playerIndex];
        if ($player->getCurrentBet() === $this->currentBet) {
            return true;
        } else {
            return false;
        }
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
    public function getPlayerCards($playerIndex): Hand
    {
        return $this->players[$playerIndex]->getHand();
    }

    /**
     * Get dealer cards.
     */
    public function getDealerCards(): Hand
    {
        return $this->dealerCards;
    }

    /**
     * Get deck.
     */
    public function getDeck(): Deck
    {
        return $this->deck;
    }

    /**
     * Get pot.
     */
    public function getPot(): int
    {
        return $this->pot;
    }

    /**
     * Get current player index.
     */
    public function getCurrPlayerIndex(): int
    {
        return $this->currPlayerIndex;
    }

    /**
     * Get phase.
     */
    public function getPhase(): int
    {
        return $this->phase;
    }

    /**
     * Get current bet.
     */
    public function getCurrentBet(): int
    {
        return $this->currentBet;
    }

    /**
     * Get number of players.
     */
    public function getNumPlayers(): int
    {
        return $this->numPlayers;
    }

    /**
     * Check if the game is over.
     */
    public function isOver(): bool
    {
        if ($this->winner === -1) {
            return false;
        }
        return true;
    }

    /**
     * Get winning player
     */
    public function getWinner(): int
    {
        return $this->winner;
    }
}
