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
    private array $playLog;

    public function __construct(int $startingMoney, string $playerName)
    {
        $this->deck = new Deck(true);
        $this->deck->shuffle();
        $this->players = [];
        for ($i = 0; $i < $this->numPlayers; $i++) {
            $name = "Player " . $i + 1;
            $computer = $i !== 0;

            if ($i == 0) {
                $name = $playerName;
            }
            $this->players[] = new Player($name, $startingMoney, $computer);
        }
        $this->pot = 0;
        $this->currPlayerIndex = 1;
        $this->phase = 0;
        $this->currentBet = 0;
        $this->winner = -1;
        $this->playLog = [];
        $this->dealerCards = new Hand();

        $this->dealToPlayers();

        $this->players[3]->makeBet($this->smallBlind);
        $this->currentBet += $this->smallBlind;
        $this->writeToLog(3, "small blind", $this->smallBlind);
        $this->players[2]->makeBet($this->bigBlind);
        $this->currentBet += $this->bigBlind;
        $this->writeToLog(2, "big blind", $this->bigBlind);
        $this->pot += ($this->smallBlind + $this->bigBlind);
    }

    /**
     * Deal two cards to each player.
     */
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
    public function updateGameState(): void
    {
        $player = $this->players[$this->currPlayerIndex];

        if ($player->isComputer()) {
            $this->computerPlay();
        } else {
            if (!$player->hasPlayed()) {
                return;
            }
        }

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
     * Computer play.
     */
    public function computerPlay(): void
    {
        $player =  $this->players[$this->currPlayerIndex];

        // no bet, computer should raise or check
        if ($this->canCheck($this->currPlayerIndex)) {
            $decision = rand(0, 1);
            if ($decision === 0) {
                $this->playerCheck($this->currPlayerIndex);
            } else {
                $raiseAmount = 100;
                $this->playerRaise($this->currPlayerIndex, $raiseAmount);
            }
        } else {
            // there is a bet, computer should fold, raise or call
            $decision = rand(0, 2);
            if ($decision === 0) {
                $this->playerFold($this->currPlayerIndex);
            } elseif ($decision === 1) {
                $this->playerCall($this->currPlayerIndex);
            } else {
                $raiseAmount = 100;
                $this->playerRaise($this->currPlayerIndex, $raiseAmount);
            }
        }
        $player->hasPlayed(true);
        return;
    }

    /**
     * Go to next phase.
     */
    public function nextPhase()
    {
        $this->phase++;
        $this->dealerCards->addCard($this->deck->draw());
        foreach ($this->players as $player) {
            $player->setPlayed(false);
        }
        $this->currPlayerIndex = $this->numPlayers - 1;
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

    public function dealerDraw(): void
    {
    }

    public function handleWin(): void
    {
    }

    /**
     * Player fold.
     */
    public function playerFold(int $playerIndex)
    {
        $player = $this->players[$playerIndex];
        $player->setFolded(true);
        $this->writeToLog($playerIndex, "fold");
        $player->hasPlayed(true);
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
        $this->writeToLog($playerIndex, "call", $callAmount);
        $player->hasPlayed(true);
    }

    /**
     * Player raise.
     */
    public function playerRaise(int $playerIndex, int $amount): void
    {
        $player = $this->players[$playerIndex];
        $total = $this->currentBet + $amount;
        $raiseAmount = $total - $player->getCurrentBet();

        $player->makeBet($raiseAmount);
        $this->pot += $raiseAmount;
        $this->currentBet = $total;
        $this->writeToLog($playerIndex, "raise", $raiseAmount);
        $player->hasPlayed(true);
    }

    /**
     * Player check.
     */
    public function playerCheck(int $playerIndex): void
    {
        $player = $this->players[$playerIndex];
        $this->writeToLog($playerIndex, "check");
        $player->setPlayed(true);
    }

    /**
     * Can a player check?
     */
    public function canCheck(int $playerIndex): bool
    {
        $player = $this->players[$playerIndex];
        return ($player->getCurrentBet() === $this->currentBet);
    }

    /**
     * Write to log.
     */
    public function writeToLog(int $playerIndex, string $action, ?int $amount = null): void
    {
        $entry = [
            "player" => $this->players[$playerIndex]->getName(),
            "playerIndex" => $playerIndex,
            "action" => $action,
            "amount" => $amount
        ];
        $this->playLog[] = $entry;
    }

    /**
     * Get log.
     */
    public function getLog(): array
    {
        return $this->playLog;
    }

    /**
     * Get players.
     */
    public function getPlayers(): array
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
