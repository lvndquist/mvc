<?php

namespace App\Proj;

use App\Proj\CardGraphic;
use App\Proj\Hand;
use App\Proj\Deck;
use App\Proj\Player;
use App\Proj\Evaluator;
use Psr\Log\LoggerInterface;
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
    private array $winners;
    private int $numPlayers = 4;
    private int $smallBlind = 20;
    private int $bigBlind = 40;
    private int $buyBack = 2500;
    private array $playLog;

    private bool $useHelp;
    private bool $useFullHelp;
    private bool $useOpenCards;

    public function __construct(
        int $startingMoney,
        string $playerName,
        bool $useHelp,
        bool $useFullHelp,
        bool $useOpenCards
    ) {
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
        $this->winners = [];
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

        if (count($this->winners) != 0) {
            return;
        }

        // let the computer play
        if ($player->isComputer() && !$player->hasPlayed() && !$player->isFolded()) {
            if ($player->isSmart()) {
                $this->smartComputerPlay($this->currPlayerIndex);
            } else {
                $this->basicComputerPlay($this->currPlayerIndex);
            }
        }

        // only one player left so handle win
        if ($this->onePlayerLeft()) {
            $this->handleWin();
            return;
        }

        // go to next phase
        if ($this->allPlayed()) {
            $this->nextPhase();
            return;
        }

        // check if valid for playing otherwise go to next player
        if ($player->isFolded() || $player->isAllIn() || $player->getMoney() === 0) {
            $this->nextPlayer();
            return;
        }

        // if human player hasnt played, return
        if (!$player->hasPlayed() && $this->currPlayerIndex == 0) {
            return;
        }


        $this->nextPlayer();
        return;
    }

    /**
     * Basic computer play. Basically no logic and does choices randomly.
     */
    public function basicComputerPlay(int $index): void
    {
        $player =  $this->players[$index];
        $raiseAmount = rand(1, 5) * 100;

        if ($player->isAllIn()) {
            return;
        }

        // no bet, computer should raise or check
        if ($this->canCheck($index)) {
            $decision = rand(0, 1);
            if ($decision === 0) {
                $this->playerCheck($index);
            } else {
                $this->playerRaise($index, $raiseAmount);
            }
        } else {
            // there is a bet, computer should fold, raise or call
            $decision = rand(0, 2);
            if ($decision === 0 && $this->phase != 0) {
                $this->playerFold($index);
            } elseif ($decision === 1 || $this->phase === 4) {
                $this->playerCall($index);
            } else {
                $this->playerRaise($index, $raiseAmount);
            }
        }
        $player->hasPlayed(true);
        return;
    }

    /**
     * Smart computer play. Makes choices based on what cards are on the table/in players hand.
     */
    public function smartComputerPlay(int $index): void
    {
        $player =  $this->players[$index];
        $this->setEvaluation($player);
        $score = $player->getEvaluatedScore();
        $name = $player->getName();
        $decision = rand(0, 3);
        $phase = $this->phase;

        $playerLogEntry = [];
        $playerLogEntry["name"] = $name;
        $playerLogEntry["score"] = $score;
        $playerLogEntry["phase"] = $phase;

        $playerLogEntry["randDecision"] = $decision;

        if ($player->isAllIn()) {
            $player->hasPlayed(true);
            return;
        }

        if ($this->canCheck($index)) {
            $playerLogEntry["possibility"] = "check/raise";
            if ($score === 10) {
                $amount = $player->getMoney();
                $this->playerRaise($index, $amount);
                $playerLogEntry["takenAction"] = "raise by {$amount} (all in)";
            } elseif ($score >= 8) {
                $amount = intdiv($player->getMoney(), 2);
                $this->playerRaise($index, $amount);
                $playerLogEntry["takenAction"] = "raise by {$amount}";
            } elseif ($score >= 6) {
                $amount = intdiv($player->getMoney(), 4);
                $this->playerRaise($index, $amount);
                $playerLogEntry["takenAction"] = "raise by {$amount}";
            } elseif ($score >= 3) {
                $amount = rand(1, 5) * 100;
                $this->playerRaise($index, $amount);
                $playerLogEntry["takenAction"] = "raise by {$amount}";
            } else {
                if ($decision === 1) {
                    $amount = rand(1, 3) * 100;
                    $this->playerRaise($index, $amount);
                    $playerLogEntry["takenAction"] = "raise by {$amount}";
                }
                $this->playerCheck($index);
                $playerLogEntry["takenAction"] = "check";
            }
        } else {
            // there is a bet, computer should fold, raise or call
            $callAmount = $this->currentBet - $player->getCurrentBet();
            $odds = $callAmount/($this->pot + $callAmount);

            $playerLogEntry["possibility"] = "fold/raise/call";
            $playerLogEntry["callAmount"] = $callAmount;
            $playerLogEntry["odds"] = $odds;
            $playerLogEntry["extra"] = [$callAmount, $this->currentBet, $player->getCurrentBet(), $this->pot];

            if ($score >= 8) {
                if ($odds < 0.3) {
                    if ($phase !== 4) {
                        $amount = $player->getMoney();
                        $this->playerRaise($index, $amount);
                        $playerLogEntry["takenAction"] = "raise by {$amount} (all in)";
                    } else {
                        $this->playerCall($index);
                        $playerLogEntry["takenAction"] = "call by {$callAmount}";
                    }
                } else {
                    if ($phase !== 4) {
                        $amount = $intdiv($player->getMoney(), 2);
                        $this->playerRaise($index, $amount);
                        $playerLogEntry["takenAction"] = "raise by {$amount}";
                    } else {
                        $this->playerCall($index);
                        $playerLogEntry["takenAction"] = "call by {$callAmount}";
                    }
                }
            } elseif ($score >= 5) {
                if ($decision === 0 || $odds < 0.3 && $phase !== 4) {
                    $amount = rand(1, 8) * 100;
                    $this->playerRaise($index, $amount);
                    $playerLogEntry["takenAction"] = "raise by {$amount}";
                } else {
                    $this->playerCall($index);
                    $playerLogEntry["takenAction"] = "call by {$callAmount}";
                }
            } elseif ($score >= 2) {
                if ($callAmount <= $player->getMoney() && $odds < 0.4 || $this->phase === 0) {
                    $this->playerCall($index);
                    $playerLogEntry["takenAction"] = "call by {$callAmount}";
                } else {
                    $this->playerFold($index);
                    $playerLogEntry["takenAction"] = "fold";
                }
            } else {
                // bluff
                if ($phase === 0 || $phase === 1 || $phase === 4) {
                    $this->playerCall($index);
                    $playerLogEntry["takenAction"] = "call by {$callAmount}";
                } elseif ($odds < 0.3) {
                    $amount = rand(1, 5) * 100;
                    $this->playerRaise($index, $amount);
                    $playerLogEntry["takenAction"] = "raise by {$amount}";
                } else {
                    $this->playerFold($index);
                    $playerLogEntry["takenAction"] = "fold";
                }
            }
        }
        $player->setComputerLog($playerLogEntry);
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
            $this->checkBets();
            $this->handleWin();
            return;
        } else {
            $this->dealerCards->addCard($this->deck->draw());
        }
        foreach ($this->players as $player) {
            $player->setPlayed(false);
        }
        $this->nextPlayer();
        //$this->currPlayerIndex = $this->numPlayers - 1;
    }

    /**
     * In final round, if there are new bets the players who havent matched those bets
     * need to be able to play again
     */
    public function checkBets(): void
    {
        $players = $this->players;
        $currentBet = $this->currentBet;
        foreach ($players as $index => $player) {
            if ($player->isComputer() && !$player->isFolded() && !$this->canCheck($index)) {
                if ($player->isSmart()) {
                    $this->smartComputerPlay($index);
                } else {
                    $this->basicComputerPlay($index);
                }
            }
        }
    }

    /**
     * Update the currPlayerIndex, ignoring players who have folded or are all in.
     */
    public function nextPlayer(): void
    {
        $nextIndex = $this->currPlayerIndex;
        $count = 0;

        while($count < $this->numPlayers) {
            $nextIndex = ($nextIndex - 1 + $this->numPlayers) % $this->numPlayers;
            $player = $this->players[$nextIndex];

            if (!$player->isFolded() && !$player->isAllIn()) {
                $this->currPlayerIndex = $nextIndex;
                return;
                //break;
            }
            $count++;
        }

        // no one can play...
        $this->nextPhase();


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
            if (!$player->isFolded() && ($player->getMoney() > 0 || $player->isAllIn())) {
                $count += 1;
            }
        }
        return ($count === 1);
    }

    /**
     * Handle a win.
     */
    public function handleWin(): void
    {
        $players =  $this->players;
        $validPlayers = array_filter($players, fn($player) => !$player->isFolded());

        $evaluator = new Evaluator();
        $this->winners = $evaluator->evaluateWinners($validPlayers);
        $winners = $this->winners;
        foreach ($winners as $winner) {
            $this->writeToLog($winner, "win", intdiv($this->pot, count($winners)));
        }
    }

    public function nextRound(): void
    {
        $players =  $this->players;
        $deck = $this->deck;
        $numPlayers = $this->numPlayers;
        $this->playLog = [];

        if ($deck->size() < 5 + $numPlayers * 2) {
            $this->deck = new Deck(true);
            $this->deck->shuffle();
        }

        foreach ($players as $index => $player) {
            $player->newRound();
            if (in_array($index, $this->winners)) {
                $player->setMoney($player->getMoney() + intdiv($this->pot, count($this->winners)));
            } elseif ($player->getMoney() < $this->bigBlind) {
                // buy back in
                $player->setMoney($this->buyBack);
                $this->writeToLog($index, "buy in", $this->buyBack);
            }
        }
        //$this->deck = new Deck(true);
        //$this->deck->shuffle();
        $this->pot = 0;
        $this->currPlayerIndex = 1;
        $this->phase = 0;
        $this->currentBet = 0;
        $this->winners = [];
        $this->dealerCards = new Hand();

        $this->dealToPlayers();
        $this->playerBlind(3, "small blind", $this->smallBlind);
        $this->playerBlind(2, "big blind", $this->bigBlind);
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
        $callAmount = max(0, $callAmount);
        $playerMoney = $player->getMoney();

        if ($callAmount >= $playerMoney) {
            $callAmount = $playerMoney;
            $this->writeToLog($playerIndex, "all in (call)", $callAmount);
        } else {
            $this->writeToLog($playerIndex, "call", $callAmount);
        }

        $player->makeBet($callAmount);
        $this->pot += $callAmount;

        $player->hasPlayed(true);
    }

    /**
     * Player raise.
     */
    public function playerRaise(int $playerIndex, int $amount): void
    {
        $player = $this->players[$playerIndex];
        $handString = $player->getEvaluatedString();

        $playerBet = $player->getCurrentBet();
        $total = $this->currentBet + $amount;

        $raiseAmount = $total - $playerBet;

        if ($raiseAmount >= $player->getMoney()) {
            $raiseAmount = $player->getMoney();
            $this->writeToLog($playerIndex, "all in (raise)", $raiseAmount);
        } else {
            $this->writeToLog($playerIndex, "raise", $raiseAmount);
        }

        $player->makeBet($raiseAmount);
        $this->pot += $raiseAmount;
        $this->currentBet = max($this->currentBet, $player->getCurrentBet());

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

    public function canRaise(int $playerIndex): bool
    {
        $player = $this->players[$playerIndex];

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
        if (empty($this->winners)) {
            return false;
        }
        return true;
    }

    /**
     * Get winning player
     */
    public function getWinner(): array
    {
        return $this->winners;
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
