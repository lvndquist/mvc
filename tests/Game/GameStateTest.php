<?php

namespace App\Game;

use PHPUnit\Framework\TestCase;
use App\Card\DeckOfCards;
use App\Game\Player;

/**
 * Test cases for class GameState.
 */
class GameStateTest extends TestCase
{
    /**
     * Construct GameState object
     */
    public function testCreateGameState(): void
    {
        $game = new GameState();
        $this->assertInstanceOf("\App\Game\GameState", $game);

        $player = $game->getPlayer();
        $this->assertInstanceOf("\App\Game\Player", $player);

        $bank = $game->getBank();
        $this->assertInstanceOf("\App\Game\Player", $bank);

        $deck = $game->getDeck();
        $this->assertInstanceOf("\App\Card\DeckOfCards", $deck);

        $draws = $game->getDrawCounter();
        $expDraws = 0;
        $this->assertEquals($expDraws, $draws);

        $gameOver = $game->gameIsOver();
        $this->assertFalse($gameOver);

        $winner = $game->getWinner();
        $expWinner = -1;
        $this->assertEquals($expWinner, $winner);
    }

    /**
     * Draw card from deck
     */
    public function testPlayerDraw(): void
    {
        $game = new GameState();
        $game->playerDraw();
        $deck = $game->getDeck();
        $deckSize = $deck->size();
        $expDeckSize = 51;
        $player = $game->getPlayer();
        $hand = $player->getHand();
        $draws = $game->getDrawCounter();
        $expDraws = 1;
        $this->assertNotEmpty($hand);
        $this->assertEquals($expDeckSize, $deckSize);
        $this->assertEquals($expDraws, $draws);
    }


    /**
     * Set winner.
     */
    public function testSetWinner(): void
    {
        $game = new GameState();
        $game->setWinner(1);
        $winner = $game->getWinner();
        $expWinner = 1;
        $this->assertEquals($expWinner, $winner);
    }


    /**
     * Player stop -> initiates bank to play.
     */
    public function testPlayerStop(): void
    {
        $game = new GameState();
        $game->playerDraw();
        $game->playerStop();
        $bankScore = $game->getBank()->getScore();
        $playerScore = $game->getPlayer()->getScore();
        $winner = $game->getWinner();
        $gameOver = $game->gameIsOver();
        if ($bankScore > 21) {
            $expWinner = 1;
            $this->assertEquals($expWinner, $winner);
            return;
        }
        $expWinner = ($playerScore <= $bankScore) ? 0 : 1;
        $this->assertEquals($expWinner, $winner);
        $this->assertTrue($gameOver);
    }


    /**
     * Player draw multiple cards.
     */
    public function testSetScore(): void
    {
        $game = new GameState();
        $player = $game->getPlayer();
        $player->setScore(20);
        $score = $player->getScore();
        $this->assertEquals($score, 20);
    }

    /**
     *  Player win by player score > bank score.
     */
    public function testPlayerWin(): void
    {
        $game = new GameState();
        $player = $game->getPlayer();
        $player->setScore(21);
        $bank = $game->getBank();
        $bank->setScore(20);
        $game->bankPlay();
        $winner = $game->getWinner();
        $this->assertEquals($winner, 1);
        $gameOver = $game->gameIsOver();
        $this->assertTrue($gameOver);
    }

    /**
     *  Bank win by player score == bank score.
     */
    public function testEqualScore(): void
    {
        $game = new GameState();
        $player = $game->getPlayer();
        $player->setScore(21);
        $bank = $game->getBank();
        $bank->setScore(21);
        $game->bankPlay();
        $winner = $game->getWinner();
        $this->assertEquals($winner, 0);
        $gameOver = $game->gameIsOver();
        $this->assertTrue($gameOver);
    }

    /**
     *  Player win by bank score > 21.
     */
    public function testBankHighScore(): void
    {
        $game = new GameState();
        $player = $game->getPlayer();
        $player->setScore(20);
        $bank = $game->getBank();
        $bank->setScore(22);
        $game->bankPlay();
        $winner = $game->getWinner();
        $this->assertEquals($winner, 1);
        $gameOver = $game->gameIsOver();
        $this->assertTrue($gameOver);
    }

    /**
     *  Bank win by player score > 21.
     */
    public function testPlayerHighScore(): void
    {
        $game = new GameState();
        $player = $game->getPlayer();
        $player->setScore(22);
        $bank = $game->getBank();
        $bank->setScore(19);
        $game->bankPlay();
        $winner = $game->getWinner();
        $this->assertEquals($winner, 1);
        $gameOver = $game->gameIsOver();
        $this->assertTrue($gameOver);
    }

    /**
     * Player draw multiple cards.
     */
    public function testPlayerDrawMany(): void
    {
        $game = new GameState();
        $player = $game->getPlayer();
        $player->setScore(20);
        $game->playerDraw();
        $game->playerDraw();
        $winner = $game->getWinner();

        $this->assertEquals($winner, 0);
        $gameOver = $game->gameIsOver();
        $this->assertTrue($gameOver);
    }

}
