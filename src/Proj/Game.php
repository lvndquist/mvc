<?php

namespace App\Proj;

use App\Proj\CardGraphic;
use App\Proj\Hand;
use App\Proj\Deck;
use App\Proj\Player;
use App\Proj\Evaluator;
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

    private bool $useHelp;
    private bool $useFullHelp;
    private bool $useOpenCards;

    public function __construct(int $startingMoney, string $playerName, bool $useHelp, bool $useFullHelp, bool $useOpenCards)
    {
        $this->deck = new Deck(true);
        $this->deck->shuffle();
        $this->players = [];
        for ($i = 0; $i < $this->numPlayers; $i++) {
            $name = "Player " . $i + 1;
            $computer = $i !== 0;
            $smart = $i % 2 !== 0;
            if ($i == 0) {
                $name = $playerName;
            }
            $this->players[] = new Player($name, $startingMoney, $computer, $smart);
        }
        $this->pot = 0;
        $this->currPlayerIndex = 1;
        $this->phase = 0;
        $this->currentBet = 0;
        $this->winner = -1;
        $this->playLog = [];
        $this->dealerCards = new Hand();
        $this->useHelp = $useHelp;
        $this->useFullHelp = $useFullHelp;
        $this->useOpenCards = $useOpenCards;

        $this->dealToPlayers();

        $this->playerBlind(3, "small blind", $this->smallBlind);
        $this->playerBlind(2, "big blind", $this->bigBlind);
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
        $this->setEvaluation($player);


        if ($this->allPlayed() || $this->onePlayerLeft()) {
            if ($this->onePlayerLeft()) {
                $this->winner = $this->currPlayerIndex;
                $this->handleWin();
                return;
            }
            $this->nextPhase();
            return;
        }
        if ($player->isFolded()) {
            $this->nextPlayer();
            return;
        }
        if (!$player->hasPlayed() && $this->currPlayerIndex == 0) {
            return;
        }
        if ($player->isComputer() && !$player->hasPlayed() && !$player->isFolded()) {
            if ($player->isSmart()) {
                $this->smartComputerPlay();
            } else {
                $this->basicComputerPlay();
            }
        }

        $this->nextPlayer();
        return;
    }

    /**
     * Basic computer play. Basically no logic and does choices randomly.
     */
    public function basicComputerPlay(): void
    {
        $player =  $this->players[$this->currPlayerIndex];
        $raiseAmount = rand(1, 5) * 100;
        // no bet, computer should raise or check
        if ($this->canCheck($this->currPlayerIndex)) {
            $decision = rand(0, 1);
            if ($decision === 0) {
                $this->playerCheck($this->currPlayerIndex);
            } else {
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
                $this->playerRaise($this->currPlayerIndex, $raiseAmount);
            }
        }
        $player->hasPlayed(true);
        return;
    }

    /**
     * Smart computer play. Makes choices based on what cards are on the table/in players hand.
     */
    public function smartComputerPlay(): void
    {
        $player =  $this->players[$this->currPlayerIndex];
        $this->setEvaluation($player);
        $score = $player->getEvaluatedScore();
        if ($this->canCheck($this->currPlayerIndex)) {
            if ($score === 10) {
                $this->playerRaise($this->currPlayerIndex, $player->getMoney());
            } elseif ($score >= 8) {
                $this->playerRaise($this->currPlayerIndex, intdiv($player->getMoney(), 2));
            } elseif ($score >= 6) {
                $this->playerRaise($this->currPlayerIndex, intdiv($player->getMoney(), 4));
            } elseif ($score >= 3) {
                $this->playerRaise($this->currPlayerIndex, rand(1, 5) * 100);
            } else {
                $this->playerCheck($this->currPlayerIndex);
            }
        } else {
            // there is a bet, computer should fold, raise or call
            $callAmount = $this->currentBet - $player->getCurrentBet();
            if ($score >= 8) {
                $this->playerRaise($this->currPlayerIndex, $player->getMoney());
            } elseif ($score >= 5) {
                $this->playerCall($this->currPlayerIndex);
            } elseif ($score >= 2) {
                if ($callAmount <= intdiv($player->getMoney(), 4)) {
                    $this->playerCall($this->currPlayerIndex);
                } else {
                    $this->playerFold($this->currPlayerIndex);
                }
            } else {
                $decision = rand(0, 2);
                // bluff
                if ($this->phase === 0) {
                    $this->playerCall($this->currPlayerIndex);
                } elseif ($decision === 0) {
                    $this->playerRaise($this->currPlayerIndex, rand(1, 5) * 100);
                } else {
                    $this->playerFold($this->currPlayerIndex);
                }
            }
        }
        $player->hasPlayed(true);
        return;
    }

    public function setEvaluation(Player $player) {
        $allCards = array_merge($player->getHand(), $this->getDealerCards());
        $evaluator = new Evaluator();
        [$handString, $score, $cards] = $evaluator->evaluateCards($allCards);
        $player->setEvaluation($handString, $score, $cards);
    }

    /**
     * Go to next phase.
     */
    public function nextPhase()
    {
        $this->phase++;
        $phase = $this->phase;
        // flop: dealer puts 3 cards
        if ($phase === 1) {
            $this->dealerCards->addCard($this->deck->draw());
            $this->dealerCards->addCard($this->deck->draw());
            $this->dealerCards->addCard($this->deck->draw());
        } elseif ($phase === 4) {
            $this->handleWin();
        } else {
            $this->dealerCards->addCard($this->deck->draw());
        }
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

    public function handleWin(): void
    {
        $players =  $this->players;
        $evaluator = new Evaluator();
        $res = $evaluator->evaluateWinners($players);

        $this->winner = 1;
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
        $handString = $player->getEvaluatedString();

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
     * Put down blind
     */
    public function playerBlind(int $playerIndex, string $blind, int $amount): void
    {
        $player = $this->players[$playerIndex];
        $player->makeBet($amount);
        $this->currentBet += $amount;
        $this->writeToLog($playerIndex, $blind, $amount);

        $this->pot += $amount;
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
    public function writeToLog(int $playerIndex, string $action, ?int $amount = null, ?string $optional = null): void
    {
        $entry = [
            "player" => $this->players[$playerIndex]->getName(),
            "playerIndex" => $playerIndex,
            "action" => $action,
            "amount" => $amount,
            "optional" => $optional
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
    public function getDealerCards(): array
    {
        return $this->dealerCards->getCards();
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

    /**
     * Get useHelpflag.
     */
    public function getUseHelp(): bool
    {
        return $this->useHelp;
    }

    /**
     * Get useFullHelp flag.
     */
    public function getUseFullHelp(): bool
    {
        return $this->useFullHelp;
    }

    /**
     * Get useOpenCards flag.
     */
    public function getUseOpenCards(): bool
    {
        return $this->useOpenCards;
    }

    /**
     * Set useHelp flag.
     */
    public function setUseHelp(bool $val): void
    {
        $this->useHelp = $val;
    }

    /**
     * Set useFullHelp flag.
     */
    public function setUseFullHelp(bool $val): void
    {
        $this->useFullHelp = $val;
    }

    /**
     * Set useOpenCards flag.
     */
    public function setUseOpenCards(bool $val): void
    {
        $this->useOpenCards = $val;
    }
}
