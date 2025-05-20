<?php

namespace App\Game;

use App\Card\DeckOfCards;
use App\Game\Player;
use Exception;

/**
 * Class representing the state of a 21 card game.
 */
class GameState
{
    /**
     * Deck of cards used in the game.
     * @var DeckOfCards
     */
    private DeckOfCards $deck;

    /**
     * The player of the game.
     * @var Player
     */
    private Player $player;

    /**
     *  The bank of the game.
     *  @var Player
     */
    private Player $bank;

    //private int $numberOfPlayers;

    /**
     * Counts the number of draws made.
     */
    private int $drawCounter;

    /**
     * Flag for tracking if the game has ended or not.
     */
    private bool $gameOver;

    /**
     * The game's winner. 0 means bank won. 1 means player won.
     * -1 means no winner has been set.
     */
    private int $gameWinner;

    /**
     * Initiate the GameState object.
     */
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

    /**
     * Player draw a card from the deck. Puts the card into
     * players hand. If score is > 21 bank wins automatically.
     */
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

    /**
     * Set the winner of the game and set flag to indicate
     * that the game is over.
     */
    public function setWinner(int $winner): void
    {
        $this->gameWinner = $winner;
        $this->gameOver = true;
    }

    /**
     * Player has finished their turns, let the bank play.
     */
    public function playerStop(): void
    {
        $this->bankPlay();
    }

    /**
     * Bank plays. Simulates drawing cards. Bank draws cards as long
     * as its score is below 17. When bank is done, the winner is decided.
     */
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
            return;
        }
        $this->setWinner($playerScore <= $bankScore ? 0 : 1);
        return;
    }

    /**
     * Get the number of draws.
     */
    public function getDrawCounter(): int
    {
        return $this->drawCounter;
    }

    /**
     * Get the gameOver flag.
     */
    public function gameIsOver(): bool
    {
        return $this->gameOver;
    }

    /**
     * Get the game winner: 0 is bank, 1 is player, -1 is undecided.
     */
    public function getWinner(): int
    {
        return $this->gameWinner;
    }

    /**
     * Get the game deck.
     */
    public function getDeck(): DeckOfCards
    {
        return $this->deck;
    }

    /**
     * Get the bank object.
     */
    public function getBank(): Player
    {
        return $this->bank;
    }

    /**
     * Get the player object.
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }
}
