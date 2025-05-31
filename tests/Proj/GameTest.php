<?php

namespace App\Proj;

use App\Proj\Hand;
use App\Proj\Game;
use App\Proj\Player;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for class CardHand.
 */
class GameTest extends TestCase
{
    /**
     * Construct game object
     */
    public function testCreateGame(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $this->assertInstanceOf("\App\Proj\Game", $game);

        $log = $game->getLog();
        $this->assertIsArray($log);

        $players = $game->getPlayers();
        $this->assertIsArray($players);
        $this->assertCount(4, $players);
        foreach($players as $index => $player) {
            $this->assertInstanceOf("\App\Proj\Player", $player);
            $playerCards = $game->getPlayerCards($index);
            $this->assertCount(2, $playerCards);
        }

        $deck = $game->getDeck();
        $this->assertCount(44, $deck->getCards());

        $pot = $game->getPot();
        $this->assertEquals(60, $pot);

        $currPlayerIndex = $game->getCurrPlayerIndex();
        $this->assertEquals(1, $currPlayerIndex);

        $phase = $game->getPhase();
        $this->assertEquals(0, $phase);

        $currBet = $game->getCurrentBet();
        $this->assertEquals(60, $currBet);

        $numPlayers = $game->getNumPlayers();
        $this->assertEquals(4, $numPlayers);

        $isOver = $game->isOver();
        $this->assertFalse($isOver);

        $winners = $game->getWinner();
        $this->assertIsArray($winners);
        $this->assertEmpty($winners);

        $useHelp = $game->getUseHelp();
        $this->assertFalse($useHelp);
        $game->setUseHelp(true);
        $this->assertTrue($game->getUseHelp());

        $fullHelp = $game->getUseFullHelp();
        $this->assertFalse($fullHelp);
        $game->setUseFullHelp(true);
        $this->assertTrue($game->getUseFullHelp());

        $openCards = $game->getUseOpenCards();
        $this->assertFalse($openCards);
        $game->setUseOpenCards(true);
        $this->assertTrue($game->getUseOpenCards());
    }

    public function testUpdateGameState(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $this->assertInstanceOf("\App\Proj\Game", $game);
        $potPre = $game->getPot();
        $currPlayerIndexPre = $game->getCurrPlayerIndex();

        $game->updateGameState();

        $players = $game->getPlayers();
        $this->assertTrue($players[$currPlayerIndexPre]->hasPlayed());

        $potPost = $game->getPot();
        $currPlayerIndexPost = $game->getCurrPlayerIndex();
        $this->assertNotEquals($potPre, $potPost);
        $this->assertEquals(0, $currPlayerIndexPost);
    }

    public function testBasicComputerWithBet(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $index = $game->getCurrPlayerIndex();
        $game->basicComputerPlay($index);
        $players = $game->getPlayers();
        $player = $players[$index];
        $played = $player->hasPlayed();
        $this->assertTrue($played);
        $log = $game->getLog();

        $this->assertEquals($log[2]["player"], "Player 2");
        $this->assertEquals($log[2]["playerIndex"], 1);
        $valid = $log[2]["action"] == "call" || $log[2]["action"] == "raise";
        $this->assertTrue($valid);
    }

    public function testBasicComputerAllIn(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $index = $game->getCurrPlayerIndex();
        $players = $game->getPlayers();
        $player = $players[$index];
        $player->setAllIn(true);
        $logPre = $game->getLog();
        $game->basicComputerPlay($index);
        $logPost = $game->getLog();
        $this->assertEquals($logPre, $logPost);
    }

    public function testPlayThroughSmartManyTimes(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $players = $game->getPlayers();
        foreach($players as $player) {
            $player->setSmart(true);
            $player->setComputer(true);
        }

        $count = 0;
        while(true) {
            $game->updateGameState();
            if ($game->isOver()) {
                $this->assertNotEmpty($game->getWinner());
                $game->nextRound();
                $count++;

                if ($count === 10) {
                    break;
                }
            }
        }
    }

    public function testPlayThroughSmart(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $players = $game->getPlayers();
        foreach($players as $player) {
            $player->setSmart(true);
            $player->setComputer(true);
        }

        while(true) {
            $game->updateGameState();
            if ($game->isOver()) {
                break;
            }
        }

        $this->assertTrue($game->isOver());
        $this->assertNotEmpty($game->getWinner());
        $game->nextRound();
        $this->assertEquals(60, $game->getPot());
        $this->assertEquals(1, $game->getCurrPlayerIndex());
        $this->assertEquals(0, $game->getPhase());
        $this->assertEquals(60, $game->getCurrentBet());
        $this->assertEquals([], $game->getWinner());
    }

    public function testSmartComputerAllIn(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $players = $game->getPlayers();
        $index = $game->getCurrPlayerIndex();
        $player = $players[$index];
        $player->setAllIn(true);

        $game->smartComputerPlay($index);

        $this->assertTrue($player->hasPlayed());
    }

    public function testAllPlayed(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $players = $game->getPlayers();
        foreach($players as $player) {
            $player->setFolded(true);
            $player->setPlayed(true);
        }
        $allPlayed = $game->allPlayed();
        $this->assertTrue($allPlayed);
    }


    public function testPlayerFold(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $index = $game->getCurrPlayerIndex();
        $game->playerFold($index);
        $players = $game->getPlayers();
        $player = $players[$index];
        $folded = $player->isFolded();
        $played = $player->hasPlayed();
        $log = $game->getLog();

        $this->assertTrue($folded);
        $this->assertTrue($played);
        $this->assertEquals($log[2]["player"], "Player 2");
        $this->assertEquals($log[2]["playerIndex"], 1);
        $this->assertEquals($log[2]["action"], "fold");
    }

    public function testPlayerCall(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $index = $game->getCurrPlayerIndex();
        $game->playerCall($index);
        $players = $game->getPlayers();
        $player = $players[$index];
        $played = $player->hasPlayed();
        $log = $game->getLog();
        $pot = $game->getPot();

        $this->assertEquals(120, $pot);
        $this->assertEquals(60, $player->getCurrentBet());
        $this->assertEquals(4940, $player->getMoney());
        $this->assertTrue($played);
        $this->assertEquals($log[2]["player"], "Player 2");
        $this->assertEquals($log[2]["playerIndex"], 1);
        $this->assertEquals($log[2]["action"], "call");
    }

    public function testPlayerCallAllIn(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $index = $game->getCurrPlayerIndex();
        $game->setCurrentBet(5100);
        $game->playerCall($index);
        $players = $game->getPlayers();
        $player = $players[$index];
        $played = $player->hasPlayed();
        $log = $game->getLog();
        $pot = $game->getPot();

        $this->assertEquals(5060, $pot);
        $this->assertEquals(5000, $player->getCurrentBet());
        $this->assertEquals(0, $player->getMoney());
        $this->assertTrue($played);
        $this->assertEquals($log[2]["player"], "Player 2");
        $this->assertEquals($log[2]["playerIndex"], 1);
        $this->assertEquals($log[2]["action"], "all in (call)");
    }

    public function testPlayerRaise(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $index = $game->getCurrPlayerIndex();
        $game->setCurrentBet(5100);
        $game->playerRaise($index, 20);
        $players = $game->getPlayers();
        $player = $players[$index];
        $played = $player->hasPlayed();
        $log = $game->getLog();
        $pot = $game->getPot();

        $this->assertEquals(5060, $pot);
        $this->assertEquals(5000, $player->getCurrentBet());
        $this->assertEquals(0, $player->getMoney());
        $this->assertTrue($played);
        $this->assertEquals($log[2]["player"], "Player 2");
        $this->assertEquals($log[2]["playerIndex"], 1);
        $this->assertEquals($log[2]["action"], "all in (raise)");
    }

    public function testPlayerRaiseAllIn(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $index = $game->getCurrPlayerIndex();
        $game->playerRaise($index, 20);
        $players = $game->getPlayers();
        $player = $players[$index];
        $played = $player->hasPlayed();
        $log = $game->getLog();
        $pot = $game->getPot();

        $this->assertEquals(140, $pot);
        $this->assertEquals(80, $player->getCurrentBet());
        $this->assertEquals(4920, $player->getMoney());
        $this->assertTrue($played);
        $this->assertEquals($log[2]["player"], "Player 2");
        $this->assertEquals($log[2]["playerIndex"], 1);
        $this->assertEquals($log[2]["action"], "raise");
    }

    public function testPlayerCheck(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $index = $game->getCurrPlayerIndex();
        $game->playerCheck($index);

        $players = $game->getPlayers();
        $player = $players[$index];
        $played = $player->hasPlayed();
        $log = $game->getLog();

        $this->assertTrue($played);
        $this->assertEquals($log[2]["player"], "Player 2");
        $this->assertEquals($log[2]["playerIndex"], 1);
        $this->assertEquals($log[2]["action"], "check");
    }

    public function testCanCheck(): void
    {
        $game = new Game(5000, "test", false, false, false);
        $index = $game->getCurrPlayerIndex();
        $canCheck = $game->canCheck($index);
        $this->assertFalse($canCheck);
    }

}
