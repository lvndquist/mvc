<?php

namespace App\Proj;

use App\Proj\Card;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Player.
 */
class PlayerTest extends TestCase
{
    /**
     * Construct player object without arguments.
     */
    public function testCreatePlayer(): void
    {
        $player = new Player("test", 5000, false, false);
        $this->assertInstanceOf("\App\Proj\Player", $player);
        $hand = $player->getHand();
        $name = $player->getName();
        $money = $player->getMoney();
        $currentBet = $player->getCurrentBet();
        $isFolded = $player->isFolded();
        $allIn = $player->isAllIn();
        $played = $player->hasPlayed();
        $computer = $player->isComputer();
        $smart = $player->isSmart();
        $evaluation = $player->getEvaluation();
        $computerLog = $player->getComputerLog();

        $expHand = [];
        $expName = "test";
        $expMoney = 5000;
        $expCurrentBet = 0;
        $expIsFolded = false;
        $expAllIn = false;
        $expPlayed = false;
        $expComputer = false;
        $expSmart = false;
        $expComputerLog = [];
        $expEvaluation = [
            "handString" => "",
            "score" => 0,
            "cards" => []
        ];

        $this->assertEquals($expHand, $hand);
        $this->assertEquals($expName, $name);
        $this->assertEquals($expMoney, $money);
        $this->assertEquals($expCurrentBet, $currentBet);
        $this->assertEquals($expIsFolded, $isFolded);
        $this->assertEquals($expAllIn, $allIn);
        $this->assertEquals($expPlayed, $played);
        $this->assertEquals($expComputer, $computer);
        $this->assertEquals($expSmart, $smart);
        $this->assertEquals($expComputerLog, $computerLog);
        $this->assertEquals($expEvaluation, $evaluation);
    }

    /**
     * Add card to player's hand.
     */
    public function testAddCard(): void
    {
        $player = new Player("test", 5000, false, false);
        $card = new Card(14, 0);
        $player->addCard($card);
        $cards = $player->getHand();
        $value = $cards[0]->getValue();
        $color = $cards[0]->getColor();
        $expValue = 14;
        $expColor = 0;

        $this->assertEquals($expValue, $value);
        $this->assertEquals($expColor, $color);
    }

    public function testSetters(): void
    {
        $player = new Player("test", 5000, false, false);
        $player->setMoney(300);
        $player->setCurrentBet(200);
        $player->setFolded(true);
        $player->setAllIn(true);
        $player->setPlayed(true);
        $player->setSmart(true);
        $player->setEvaluation("test", 5, []);
        $player->setComputerLog(
            ["name" => "test",
             "score" => 10,
             "possibility" => "test",
             "phase" => 0
            ]
        );

        $money = $player->getMoney();
        $currentBet = $player->getCurrentBet();
        $isFolded = $player->isFolded();
        $allIn = $player->isAllIn();
        $played = $player->hasPlayed();
        $smart = $player->isSmart();
        $evaluation = $player->getEvaluation();
        $computerLog = $player->getComputerLog();
        $evaluatedString = $player->getEvaluatedString();
        $evaluatedScore = $player->getEvaluatedScore();

        $expMoney = 300;
        $expCurrentBet = 200;
        $expIsFolded = true;
        $expAllIn = true;
        $expPlayed = true;
        $expSmart = true;
        $expEvaluation = ["handString" => "test", "score" => 5, "cards" => []];
        $expComputerLog = [
            "name" => "test",
            "score" => 10,
            "possibility" => "test",
            "callAmount" => null,
            "odds" => null,
            "randDecision" => null,
            "phase" => 0,
            "takenAction" => null,
            "extra" => null
        ];
        $expEvaluatedScore = 5;
        $expEvaluatedString = "test";

        $this->assertEquals($expMoney, $money);
        $this->assertEquals($expCurrentBet, $currentBet);
        $this->assertEquals($expIsFolded, $isFolded);
        $this->assertEquals($expPlayed, $played);
        $this->assertEquals($expAllIn, $allIn);
        $this->assertEquals($expSmart, $smart);
        $this->assertEquals($evaluation, $expEvaluation);
        $this->assertEquals($expComputerLog, $computerLog[0]);
        $this->assertEquals($expEvaluatedScore, $evaluatedScore);
        $this->assertEquals($expEvaluatedString, $evaluatedString);
    }

    public function testMakeBetValid(): void
    {
        $player = new Player("test", 5000, false, false);
        $player->makeBet(300);
        $expBet = 300;
        $bet = $player->getCurrentBet();
        $this->assertEquals($expBet, $bet);
    }

    public function testMakeBetInvalid(): void
    {
        $player = new Player("test", 5000, false, false);
        $player->makeBet(5001);
        $expBet = 5000;
        $bet = $player->getCurrentBet();
        $this->assertEquals($expBet, $bet);
    }

    public function testNewRound(): void
    {
        $player = new Player("test", 5000, false, false);
        $player->setCurrentBet(200);
        $player->setFolded(true);
        $player->setAllIn(true);
        $player->setPlayed(true);
        $player->setEvaluation("test", 5, []);
        $player->setComputerLog(
            ["name" => "test",
             "score" => 10,
             "possibility" => "test",
             "phase" => 0
            ]
        );

        $expCurrentBet = false;
        $expIsFolded = false;
        $expAllIn = false;
        $expPlayed = false;
        $expEvaluation = [
            "handString" => "",
            "score" => 0,
            "cards" => []
        ];
        $expComputerLog = [];



        $player->newRound();
        $currentBet = $player->getCurrentBet();
        $isFolded = $player->isFolded();
        $allIn = $player->isAllIn();
        $played = $player->hasPlayed();
        $evaluation = $player->getEvaluation();
        $computerLog = $player->getComputerLog();

        $this->assertEquals($expCurrentBet, $currentBet);
        $this->assertEquals($expIsFolded, $isFolded);
        $this->assertEquals($expPlayed, $played);
        $this->assertEquals($expAllIn, $allIn);
        $this->assertEquals($evaluation, $expEvaluation);
        $this->assertEquals($expComputerLog, $computerLog);
    }
}
