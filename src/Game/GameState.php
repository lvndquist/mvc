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

    /** @var Player[] */
    private array $players;

    /** @var Bank */
    private Bank $bank;

    private int $numberOfPlayers;

    private int $turnIndex;

    public function __construct(int $numPlayers)
    {
        $deck = new DeckOfCards(true);
        $deck->shuffle();
        $this->deck = $deck;
        for ($num = 0; $num < $numPlayers; $num++) {
            $this->players[] = new Player();
        }

        $this->bank = new Bank();
        $this->numberOfPlayers = $numPlayers;
        $this->turnIndex = 0;
    }
}
