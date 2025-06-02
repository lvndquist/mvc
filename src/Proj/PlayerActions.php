<?php

namespace App\Proj;

use App\Proj\Game;
use App\Proj\Player;

/**
 * Class representing a hand of cards.
 */
class PlayerActions
{
    /** @var Player[] */
    private array $players;
    private Game $game;

    public function __construct(Game $game)
    {
        $this->players = $game->getPlayers();
        $this->game = $game;
    }

    /**
     * Player fold.
     */
    public function playerFold(int $playerIndex): void
    {
        $player = $this->players[$playerIndex];
        $player->setFolded(true);
        $this->game->writeToLog($playerIndex, "fold");
        $player->setPlayed(true);
    }

    /**
     * Player call.
     */
    public function playerCall(int $playerIndex): void
    {
        $player = $this->players[$playerIndex];
        $callAmount = $this->game->getCurrentBet() - $player->getCurrentBet();
        $callAmount = (int) max(0, $callAmount);
        $playerMoney = $player->getMoney();
        $pot = $this->game->getPot();

        if ($callAmount >= $playerMoney) {
            $callAmount = $playerMoney;
            $this->game->writeToLog($playerIndex, "all in (call)", $callAmount);
            $player->makeBet($callAmount);
            $this->game->setPot($pot + $callAmount);
            $player->setPlayed(true);
            return;
        }

        $this->game->writeToLog($playerIndex, "call", $callAmount);
        $player->makeBet($callAmount);
        $this->game->setPot($pot + $callAmount);
        $player->setPlayed(true);
        return;
    }

    /**
     * Player raise.
     */
    public function playerRaise(int $playerIndex, int $amount): void
    {
        $player = $this->players[$playerIndex];
        $playerBet = $player->getCurrentBet();
        $total = $this->game->getCurrentBet() + $amount;
        $pot = $this->game->getPot();

        $raiseAmount = $total - $playerBet;

        if ($raiseAmount >= $player->getMoney()) {
            $raiseAmount = $player->getMoney();
            $this->game->writeToLog($playerIndex, "all in (raise)", $raiseAmount);
            $player->makeBet($raiseAmount);
            $this->game->setPot($pot + $raiseAmount);
            $this->game->setCurrentBet(max($this->game->getCurrentBet(), $player->getCurrentBet()));
            $player->setPlayed(true);
            return;
        }

        $this->game->writeToLog($playerIndex, "raise", $raiseAmount);
        $player->makeBet($raiseAmount);
        $this->game->setPot($pot + $raiseAmount);
        $this->game->setCurrentBet(max($this->game->getCurrentBet(), $player->getCurrentBet()));
        $player->setPlayed(true);
        return;
    }

    /**
     * Player check.
     */
    public function playerCheck(int $playerIndex): void
    {
        $player = $this->players[$playerIndex];

        $this->game->writeToLog($playerIndex, "check");

        $player->setPlayed(true);
    }

    /**
     * Put down blind
     */
    public function playerBlind(int $playerIndex, string $blind, int $amount): void
    {
        $player = $this->players[$playerIndex];
        $player->makeBet($amount);
        $currentBet = $this->game->getCurrentBet();
        $pot = $this->game->getPot();
        $this->game->setCurrentBet($currentBet + $amount);
        $this->game->writeToLog($playerIndex, $blind, $amount);
        $this->game->setPot($pot + $amount);
        $player->setPlayed(true);
    }
}
