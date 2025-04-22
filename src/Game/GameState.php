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

    /** @var Player */
    private Player $bank;

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

        $this->bank = new Player();
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

    public function setWinner(int $winner): void
    {
        $this->gameWinner = $winner;
        $this->gameOver = true;
    }

    public function playerStop(): void
    {
        $this->bankPlay();
    }

    public function bankPlay(): void
    {
        $bank = $this->bank;
        while ($bank->getScore() < 17) {
            $card = $this->deck->draw();
            $bank->addCard($card);
            $this->drawCounter += 1;
        }
        $bankScore = $this->bank->getScore();
        $playerScore = $this->player->getScore();

        if ($bankScore > 21) {
            $this->setWinner(1);
        } else {
            if ($playerScore <= $bankScore) {
                $this->setWinner(0);
            } else {
                $this->setWinner(1);
            }
        }
    }

    public function getDrawCounter(): int
    {
        return $this->drawCounter;
    }

    public function gameIsOver(): bool
    {
        return $this->gameOver;
    }

    public function getWinner(): int
    {
        return $this->gameWinner;
    }

    public function getDeck(): DeckOfCards
    {
        return $this->deck;
    }

    public function getBank(): Player
    {
        return $this->bank;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}
