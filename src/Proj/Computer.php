<?php

namespace App\Proj;

use App\Proj\Game;
use App\Proj\Player;
use App\Proj\PlayerActions;

/**
 * Class representing a Card.
 */
class Computer
{
    /** @var Player[] */
    private array $players;
    private Game $game;
    private PlayerActions $actions;

    public function __construct(Game $game, PlayerActions $actions)
    {
        $this->players = $game->getPlayers();
        $this->game = $game;
        $this->actions = $actions;
    }

    /**
     * Basic computer play. Basically no logic and does choices randomly.
     */
    public function basicComputerPlay(int $index): void
    {
        $player = $this->players[$index];
        $raiseAmount = rand(1, 5) * 100;

        if ($player->isAllIn()) {
            $player->setPlayed(true);
            return;
        }

        // no bet, computer should raise or check
        if ($this->game->canCheck($index)) {
            $decision = rand(0, 1);
            if ($decision === 0) {
                $this->actions->playerCheck($index);
                $player->setPlayed(true);
                return;
            }
            $this->actions->playerRaise($index, $raiseAmount);
            $player->setPlayed(true);
            return;
        }
        // @phpstan-ignore-next-line
        if (!$this->game->canCheck($index)) {
            // there is a bet, computer should fold, raise or call
            $decision = rand(0, 2);
            if ($decision === 0 && ($this->game->getPhase() != 0)) {
                $this->actions->playerFold($index);
                $player->setPlayed(true);
                return;
            } elseif ($decision === 1 || $this->game->getPhase() === 4) {
                $this->actions->playerCall($index);
                $player->setPlayed(true);
                return;
            }
            $this->actions->playerRaise($index, $raiseAmount);
            $player->setPlayed(true);
            return;
        }
    }

    /**
     * Smart computer play. Makes choices based on what cards are on the table/in players hand.
     */
    public function smartComputerPlay(int $index): void
    {
        $player = $this->players[$index];
        $this->game->setEvaluation($player);
        $score = $player->getEvaluatedScore();
        $name = $player->getName();
        $phase = $this->game->getPhase();
        $decision = rand(0, 3);

        $playerLogEntry = [];
        $playerLogEntry["name"] = $name;
        $playerLogEntry["score"] = $score;
        $playerLogEntry["phase"] = $phase;

        $playerLogEntry["randDecision"] = $decision;

        if ($player->isAllIn()) {
            $player->setPlayed(true);
            return;
        }

        if ($this->game->canCheck($index)) {
            $this->smartComputerCheckOrRaise($score, $player, $playerLogEntry, $decision, $phase, $index);
        }
        $this->smartComputerBet($score, $player, $playerLogEntry, $decision, $phase, $index);
        return;
    }

    public function smartComputerCheckOrRaise(int $score, Player $player, array $playerLogEntry, int $decision, int $phase, $index) {
        $playerLogEntry["possibility"] = "check/raise";
        if ($score === 10) {
            $amount = $player->getMoney();
            $this->actions->playerRaise($index, $amount);
            $playerLogEntry["takenAction"] = "raise by {$amount} (all in)";
            $player->setComputerLog($playerLogEntry);
            $player->setPlayed(true);
            return;
        } elseif ($score >= 8) {
            $amount = intdiv($player->getMoney(), 2);
            $this->actions->playerRaise($index, $amount);
            $playerLogEntry["takenAction"] = "raise by {$amount}";
            $player->setComputerLog($playerLogEntry);
            $player->setPlayed(true);
            return;
        } elseif ($score >= 6) {
            $amount = intdiv($player->getMoney(), 4);
            $this->actions->playerRaise($index, $amount);
            $playerLogEntry["takenAction"] = "raise by {$amount}";
            $player->setComputerLog($playerLogEntry);
            $player->setPlayed(true);
            return;
        } elseif ($score >= 3) {
            $amount = rand(1, 5) * 100;
            $this->actions->playerRaise($index, $amount);
            $playerLogEntry["takenAction"] = "raise by {$amount}";
            $player->setComputerLog($playerLogEntry);
            $player->setPlayed(true);
            return;
        }

        if ($decision === 1) {
            $amount = rand(1, 3) * 100;
            $this->actions->playerRaise($index, $amount);
            $playerLogEntry["takenAction"] = "raise by {$amount}";
            $player->setComputerLog($playerLogEntry);
            $player->setPlayed(true);
            return;
        }

        $this->actions->playerCheck($index);
        $playerLogEntry["takenAction"] = "check";
        $player->setComputerLog($playerLogEntry);
        $player->setPlayed(true);
        return;
    }

    public function smartComputerBet(int $score, Player $player, array $playerLogEntry, int $decision, int $phase, int $index) {

        // there is a bet, computer should fold, raise or call
        $callAmount = $this->game->getCurrentBet() - $player->getCurrentBet();
        $odds = $callAmount / ($this->game->getPot() + $callAmount);

        $decision = rand(0, 3);
        $phase = $this->game->getPhase();

        $playerLogEntry["possibility"] = "fold/raise/call";
        $playerLogEntry["callAmount"] = $callAmount;
        $playerLogEntry["odds"] = $odds;
        $playerLogEntry["extra"] = [$callAmount, $this->game->getCurrentBet(), $player->getCurrentBet(), $this->game->getPot()];

        if ($score >= 8) {
            if ($odds < 0.3) {
                if ($phase !== 4) {
                    $amount = $player->getMoney();
                    $this->actions->playerRaise($index, $amount);
                    $playerLogEntry["takenAction"] = "raise by {$amount} (all in)";
                    $player->setComputerLog($playerLogEntry);
                    $player->setPlayed(true);
                    return;
                }

                $this->actions->playerCall($index);
                $playerLogEntry["takenAction"] = "call by {$callAmount}";
                $player->setComputerLog($playerLogEntry);
                $player->setPlayed(true);
                return;
            }

            if ($odds >= 0.3) {
                if ($phase !== 4) {
                    $amount = intdiv($this->$player->getMoney(), 2);
                    $this->actions->playerRaise($index, $amount);
                    $playerLogEntry["takenAction"] = "raise by {$amount}";
                    $player->setComputerLog($playerLogEntry);
                    $player->setPlayed(true);
                    return;
                }

                $this->actions->playerCall($index);
                $playerLogEntry["takenAction"] = "call by {$callAmount}";
                $player->setComputerLog($playerLogEntry);
                $player->setPlayed(true);
                return;
            }
        } elseif ($score >= 5) {
            if ($decision === 0 || $odds < 0.3 && $phase !== 4) {
                $amount = rand(1, 8) * 100;
                $this->actions->playerRaise($index, $amount);
                $playerLogEntry["takenAction"] = "raise by {$amount}";
                $player->setComputerLog($playerLogEntry);
                $player->setPlayed(true);
                return;
            }

            $this->actions->playerCall($index);
            $playerLogEntry["takenAction"] = "call by {$callAmount}";
            $player->setComputerLog($playerLogEntry);
            $player->setPlayed(true);
            return;
        } elseif ($score >= 2) {
            if (($callAmount <= $player->getMoney() && $odds < 0.4) || $phase === 0) {
                $this->actions->playerCall($index);
                $playerLogEntry["takenAction"] = "call by {$callAmount}";
                $player->setComputerLog($playerLogEntry);
                $player->setPlayed(true);
                return;
            }

            $this->actions->playerFold($index);
            $playerLogEntry["takenAction"] = "fold";
            $player->setComputerLog($playerLogEntry);
            $player->setPlayed(true);
            return;

        }

        // bluff
        if ($phase === 0 || $phase === 1 || $phase === 4) {
            $this->actions->playerCall($index);
            $playerLogEntry["takenAction"] = "call by {$callAmount}";
            $player->setComputerLog($playerLogEntry);
            $player->setPlayed(true);
            return;
        } elseif ($odds < 0.3) {
            $amount = rand(1, 5) * 100;
            $this->actions->playerRaise($index, $amount);
            $playerLogEntry["takenAction"] = "raise by {$amount}";
            $player->setComputerLog($playerLogEntry);
            $player->setPlayed(true);
            return;
        }
        $this->actions->playerFold($index);
        $playerLogEntry["takenAction"] = "fold";
        $player->setComputerLog($playerLogEntry);
        $player->setPlayed(true);
        return;
    }

}


