<?php

namespace App\Game;

use App\Card\DeckOfCards;
use App\Game\Bank;
use App\Game\Player;
use Exception;

class GameState
{
    /** @var DeckOfCards */
    private DeckOfCards $deck;

    /** @var Player */
    private Player $player;

    /** @var Bank */
    private Bank $bank;

    //private int $numberOfPlayers;

    private int $drawCounter;

    private bool $gameOver;

    // 0 for bank, 1 for player
    private int $gameWinner;

    public function __construct()
    {
        $deck = new DeckOfCards(true);
        $deck->shuffle();
        $this->deck = $deck;
        $this->player = new Player();

        $this->bank = new Bank();
        $this->drawCounter = 0;
        $this->gameOver = false;
        $this->gameWinner = -1;
    }

    public function playerDraw(): void
    {
        $player = $this->player;
        $card = $this->deck->draw();
        $player->addCard($card);
        $this->drawCounter += 1;
        if ($player->getScore() > 21) {
            $this->setWinner(0); // bank wins
        }
    }

    public function setWinner(int $winner) {
        $this->gameWinner = $winner;
        $this->gameOver = true;
    }

    public function playerStop(): void
    {
    }

    public function controlScores(): void
    {
        $playerScore = $this->player->getScore();
        $bankScore = $this->bank->getScore();
        if ($playerScore <= $bankScore) {
            $this->gameWinner = 0;
        } else {
            $this->gameWinner = 1;
        }
        $this->gameOver = true;
    }

    public function gameIsOver(): bool
    {
        return $this->gameOver;
    }

    public function getWinner()
    {
        return $this->gameWinner;
    }

    public function getDeck(): DeckOfCards
    {
        return $this->deck;
    }

    public function getBank(): Bank
    {
        return $this->bank;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}
