<?php

namespace App\Proj;

use App\Proj\Card;
use App\Proj\Player;
use App\Proj\Evaluator;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Card.
 */
class EvaluatorTest extends TestCase
{
    /**
     * Test a hand with royal flush.
     */
    public function testRoyalFlush(): void
    {
        $evaluator = new Evaluator();
        $cards = [];
        $cards[] = new Card(14, 0);
        $cards[] = new Card(12, 0);
        $cards[] = new Card(13, 0);
        $cards[] = new Card(11, 0);
        $cards[] = new Card(10, 0);
        $cards[] = new Card(4, 1);
        $cards[] = new Card(7, 2);
        $res = $evaluator->evaluateCards($cards);

        $resString = $res["handString"];
        $resScore = $res["score"];
        $resCards = $res["cards"];

        $expResString = "Royal Flush";
        $expResScore = 10;

        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals(14 - $i, $resCards[$i]->getValue());
            $this->assertEquals(0, $resCards[$i]->getColor());
        }
        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }

    /**
     * Test a hand with straight flush
     */
    public function testStraightFlush(): void
    {
        $evaluator = new Evaluator();
        $cards = [];
        $cards[] = new Card(10, 0);
        $cards[] = new Card(8, 0);
        $cards[] = new Card(9, 0);
        $cards[] = new Card(7, 0);
        $cards[] = new Card(6, 0);
        $cards[] = new Card(4, 1);
        $cards[] = new Card(7, 2);
        $res = $evaluator->evaluateCards($cards);

        $resString = $res["handString"];
        $resScore = $res["score"];
        $resCards = $res["cards"];

        $expResString = "Straight Flush";
        $expResScore = 9;

        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals(10 - $i, $resCards[$i]->getValue());
            $this->assertEquals(0, $resCards[$i]->getColor());
        }
        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }

    /**
    * Test a hand with four of a kind.
    */
    public function testFourOfAKind(): void
    {
        $evaluator = new Evaluator();
        $cards = [];
        $cards[] = new Card(10, 0);
        $cards[] = new Card(4, 1);
        $cards[] = new Card(10, 2);
        $cards[] = new Card(10, 3);
        $cards[] = new Card(6, 0);
        $cards[] = new Card(10, 1);
        $cards[] = new Card(7, 2);
        $res = $evaluator->evaluateCards($cards);

        $resString = $res["handString"];
        $resScore = $res["score"];
        $resCards = $res["cards"];

        $expResString = "Four of a kind";
        $expResScore = 8;

        for ($i = 0; $i < 4; $i++) {
            $this->assertEquals(10, $resCards[$i]->getValue());
        }
        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }

    /**
     * Test a hand with full house.
     */
    public function testFullHouse(): void
    {
        $evaluator = new Evaluator();
        $cards = [];
        $cards[] = new Card(11, 0);
        $cards[] = new Card(10, 1);
        $cards[] = new Card(10, 2);
        $cards[] = new Card(10, 0);
        $cards[] = new Card(11, 1);
        $cards[] = new Card(12, 1);
        $cards[] = new Card(7, 2);
        $res = $evaluator->evaluateCards($cards);

        $resString = $res["handString"];
        $resScore = $res["score"];
        $resCards = $res["cards"];

        $expResString = "Full house";
        $expResScore = 7;

        $this->assertEquals(10, $resCards[0]->getValue());
        $this->assertEquals(10, $resCards[1]->getValue());
        $this->assertEquals(10, $resCards[2]->getValue());
        $this->assertEquals(11, $resCards[3]->getValue());
        $this->assertEquals(11, $resCards[4]->getValue());

        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }

    /**
     * Test a hand with flush.
     */
    public function testFlush(): void
    {
        $evaluator = new Evaluator();
        $cards = [];
        $cards[] = new Card(11, 1);
        $cards[] = new Card(10, 1);
        $cards[] = new Card(10, 2);
        $cards[] = new Card(10, 1);
        $cards[] = new Card(13, 1);
        $cards[] = new Card(12, 1);
        $cards[] = new Card(7, 2);
        $res = $evaluator->evaluateCards($cards);

        $resString = $res["handString"];
        $resScore = $res["score"];
        $resCards = $res["cards"];

        $expResString = "Flush";
        $expResScore = 6;

        for ($i = 0; $i < 4; $i++) {
            $this->assertEquals(1, $resCards[$i]->getColor());
        }
        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }

    /**
     * Test a hand with a straight.
     */
    public function testStraight(): void
    {
        $evaluator = new Evaluator();
        $cards = [];
        $cards[] = new Card(11, 1);
        $cards[] = new Card(10, 3);
        $cards[] = new Card(9, 2);
        $cards[] = new Card(8, 1);
        $cards[] = new Card(13, 1);
        $cards[] = new Card(12, 1);
        $cards[] = new Card(7, 2);
        $res = $evaluator->evaluateCards($cards);

        $resString = $res["handString"];
        $resScore = $res["score"];
        $resCards = $res["cards"];

        $expResString = "Straight";
        $expResScore = 5;

        for ($i = 0; $i < 4; $i++) {
            $this->assertEquals(11 - $i, $resCards[$i]->getValue());
        }
        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }

    /**
     * Test a hand with a threeOfAKind.
     */
    public function testThreeOfAKind(): void
    {
        $evaluator = new Evaluator();
        $cards = [];
        $cards[] = new Card(11, 1);
        $cards[] = new Card(11, 3);
        $cards[] = new Card(11, 2);
        $cards[] = new Card(8, 1);
        $cards[] = new Card(13, 1);
        $cards[] = new Card(12, 1);
        $cards[] = new Card(7, 2);
        $res = $evaluator->evaluateCards($cards);

        $resString = $res["handString"];
        $resScore = $res["score"];
        $resCards = $res["cards"];

        $expResString = "Three of a kind";
        $expResScore = 4;

        for ($i = 0; $i < 2; $i++) {
            $this->assertEquals(11, $resCards[$i]->getValue());
        }
        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }

    /**
     * Test a hand with two pairs.
     */
    public function testTwoPair(): void
    {
        $evaluator = new Evaluator();
        $cards = [];
        $cards[] = new Card(11, 1);
        $cards[] = new Card(11, 3);
        $cards[] = new Card(8, 2);
        $cards[] = new Card(12, 1);
        $cards[] = new Card(13, 1);
        $cards[] = new Card(8, 1);
        $cards[] = new Card(7, 2);
        $res = $evaluator->evaluateCards($cards);

        $resString = $res["handString"];
        $resScore = $res["score"];
        $resCards = $res["cards"];

        $expResString = "Two pair";
        $expResScore = 3;

        $this->assertEquals(11, $resCards[0]->getValue());
        $this->assertEquals(11, $resCards[1]->getValue());
        $this->assertEquals(8, $resCards[2]->getValue());
        $this->assertEquals(8, $resCards[3]->getValue());
        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }

    /**
     * Test a hand with one pair.
     */
    public function testPair(): void
    {
        $evaluator = new Evaluator();
        $cards = [];
        $cards[] = new Card(11, 1);
        $cards[] = new Card(11, 3);
        $cards[] = new Card(8, 2);
        $cards[] = new Card(2, 1);
        $cards[] = new Card(3, 1);
        $cards[] = new Card(5, 3);
        $cards[] = new Card(9, 2);
        $res = $evaluator->evaluateCards($cards);

        $resString = $res["handString"];
        $resScore = $res["score"];
        $resCards = $res["cards"];

        $expResString = "Pair";
        $expResScore = 2;

        $this->assertEquals(11, $resCards[0]->getValue());
        $this->assertEquals(11, $resCards[1]->getValue());
        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }

    /**
     * Test a hand with a high card.
     */
    public function testHighCard(): void
    {
        $evaluator = new Evaluator();
        $cards = [];
        $cards[] = new Card(14, 1);
        $cards[] = new Card(11, 3);
        $cards[] = new Card(8, 2);
        $cards[] = new Card(2, 1);
        $cards[] = new Card(3, 1);
        $cards[] = new Card(5, 3);
        $cards[] = new Card(9, 2);
        $res = $evaluator->evaluateCards($cards);

        $resString = $res["handString"];
        $resScore = $res["score"];
        $resCards = $res["cards"];

        $expResString = "High card";
        $expResScore = 1;

        $this->assertEquals(14, $resCards[0]->getValue());
        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }

    /**
     * @param Player[] $players
     * @param Card[][] $cards
     * @return int[] $winners
     */
    public function evaluateWinnersSetUp(array $players, array $cards): array
    {
        $evaluator = new Evaluator();
        $count = count($players);
        for ($i = 0; $i < $count; $i++) {
            $res = $evaluator->evaluateCards($cards[$i]);
            $cardsString = $res["handString"];
            $cardsScore = $res["score"];
            $cardsCards = $res["cards"];
            $players[$i]->setEvaluation($cardsString, $cardsScore, $cardsCards);
        }
        $winners = $evaluator->evaluateWinners($players);
        return $winners;
    }


    public function testEvaluateWinnersRoyalFlushNoTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(14, 0), new Card(13, 0), new Card(12, 0), new Card(11, 0), new Card(10, 0), new Card(3, 1), new Card(2, 1)];
        $cards2 = [new Card(2, 2), new Card(3, 3), new Card(4, 0), new Card(5, 1), new Card(6, 1), new Card(7, 3), new Card(5, 2)];
        $cards3 = [new Card(3, 2), new Card(3, 1), new Card(7, 0), new Card(10, 1), new Card(8, 1), new Card(5, 3), new Card(5, 0)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(1, count($winners));
        $this->assertEquals(0, $winners[0]);
    }

    public function testEvaluateWinnersRoyalFlushWithTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(14, 0), new Card(13, 0), new Card(12, 0), new Card(11, 0), new Card(10, 0), new Card(3, 1), new Card(2, 1)];
        $cards2 = [new Card(14, 0), new Card(13, 0), new Card(12, 0), new Card(11, 0), new Card(10, 0), new Card(7, 3), new Card(5, 2)];
        $cards3 = [new Card(3, 2), new Card(3, 1), new Card(7, 0), new Card(10, 1), new Card(8, 1), new Card(5, 3), new Card(5, 0)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);

        $this->assertEquals(2, count($winners));
        $this->assertEquals(0, $winners[0]);
        $this->assertEquals(1, $winners[1]);
    }

    public function testEvaluateWinnersStraightNoTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(14, 2), new Card(13, 0), new Card(12, 0), new Card(11, 1), new Card(10, 1), new Card(3, 0), new Card(2, 0)];
        $cards2 = [new Card(2, 2), new Card(3, 3), new Card(4, 0), new Card(5, 1), new Card(6, 1), new Card(7, 3), new Card(5, 2)];
        $cards3 = [new Card(3, 2), new Card(3, 1), new Card(7, 0), new Card(10, 1), new Card(8, 1), new Card(5, 3), new Card(5, 0)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(1, count($winners));
        $this->assertEquals(0, $winners[0]);
    }

    public function testEvaluateWinnersStraightWithTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(14, 2), new Card(13, 0), new Card(12, 0), new Card(11, 1), new Card(10, 2), new Card(3, 0), new Card(2, 0)];
        $cards2 = [new Card(3, 2), new Card(3, 3), new Card(4, 0), new Card(5, 1), new Card(6, 1), new Card(7, 3), new Card(5, 2)];
        $cards3 = [new Card(14, 1), new Card(12, 1), new Card(13, 1), new Card(10, 1), new Card(11, 3), new Card(5, 3), new Card(5, 0)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(2, count($winners));
        $this->assertEquals(0, $winners[0]);
        $this->assertEquals(2, $winners[1]);
    }

    public function testEvaluateWinnersFourOfAKindNoTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(14, 2), new Card(14, 0), new Card(14, 1), new Card(14, 3), new Card(10, 2), new Card(3, 0), new Card(2, 0)];
        $cards2 = [new Card(3, 2), new Card(3, 3), new Card(4, 0), new Card(5, 1), new Card(12, 1), new Card(7, 3), new Card(5, 2)];
        $cards3 = [new Card(13, 2), new Card(13, 3), new Card(13, 2), new Card(13, 1), new Card(11, 3), new Card(5, 3), new Card(5, 0)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(1, count($winners));
        $this->assertEquals(0, $winners[0]);
    }

    public function testEvaluateWinnersFourOfAKindWithTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(14, 2), new Card(14, 0), new Card(14, 1), new Card(14, 3), new Card(10, 2), new Card(13, 0), new Card(2, 0)];
        $cards2 = [new Card(14, 2), new Card(14, 0), new Card(14, 1), new Card(14, 3), new Card(12, 1), new Card(5, 2), new Card(13, 3)];
        $cards3 = [new Card(3, 2), new Card(3, 3), new Card(4, 0), new Card(5, 1), new Card(12, 1), new Card(7, 3), new Card(5, 2)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(2, count($winners));
        $this->assertEquals(0, $winners[0]);
        $this->assertEquals(1, $winners[1]);
    }

    public function testEvaluateWinnersFullHouseNoTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(2, 0), new Card(7, 1), new Card(14, 3), new Card(12, 1), new Card(5, 2), new Card(13, 3)];
        $cards2 = [new Card(14, 2), new Card(14, 0), new Card(14, 1), new Card(13, 3), new Card(13, 2), new Card(5, 0), new Card(2, 0)];
        $cards3 = [new Card(3, 2), new Card(3, 3), new Card(3, 0), new Card(5, 1), new Card(8, 1), new Card(6, 3), new Card(5, 2)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(1, count($winners));
        $this->assertEquals(1, $winners[0]);
    }

    public function testEvaluateWinnersFullHouseWithTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(14, 2), new Card(14, 0), new Card(13, 1), new Card(14, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(14, 2), new Card(14, 0), new Card(14, 1), new Card(13, 3), new Card(13, 2), new Card(5, 0), new Card(2, 0)];
        $cards3 = [new Card(3, 2), new Card(3, 3), new Card(3, 0), new Card(5, 1), new Card(8, 1), new Card(6, 3), new Card(5, 2)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(2, count($winners));
        $this->assertEquals(0, $winners[0]);
        $this->assertEquals(1, $winners[1]);
    }

    public function testEvaluateWinnersFlushNoTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(2, 0), new Card(6, 1), new Card(9, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(14, 0), new Card(11, 0), new Card(14, 3), new Card(10, 0), new Card(7, 0), new Card(4, 0), new Card(2, 1)];
        $cards3 = [new Card(2, 1), new Card(3, 1), new Card(8, 1), new Card(5, 1), new Card(8, 3), new Card(6, 1), new Card(5, 2)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(1, count($winners));
        $this->assertEquals(1, $winners[0]);
    }

    public function testEvaluateWinnersFlushWithTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(2, 0), new Card(6, 1), new Card(9, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(14, 0), new Card(11, 0), new Card(7, 0), new Card(10, 0), new Card(3, 0), new Card(4, 2), new Card(2, 1)];
        $cards3 = [new Card(14, 1), new Card(11, 1), new Card(7, 1), new Card(10, 1), new Card(3, 1), new Card(6, 3), new Card(5, 2)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(2, count($winners));
        $this->assertEquals(1, $winners[0]);
        $this->assertEquals(2, $winners[1]);
    }

    public function testEvaluateWinnersThreeOfAKindNoTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(2, 0), new Card(6, 1), new Card(9, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(14, 0), new Card(14, 1), new Card(14, 2), new Card(2, 0), new Card(3, 0), new Card(4, 2), new Card(6, 1)];
        $cards3 = [new Card(3, 0), new Card(3, 1), new Card(3, 2), new Card(2, 1), new Card(13, 1), new Card(6, 3), new Card(5, 2)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(1, count($winners));
        $this->assertEquals(1, $winners[0]);
    }

    public function testEvaluateWinnersThreeOfAKindWithTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(2, 0), new Card(6, 1), new Card(9, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(8, 0), new Card(8, 1), new Card(8, 2), new Card(12, 0), new Card(6, 0), new Card(4, 2), new Card(2, 1)];
        $cards3 = [new Card(8, 0), new Card(8, 1), new Card(8, 2), new Card(12, 0), new Card(6, 0), new Card(4, 2), new Card(5, 2)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(2, count($winners));
        $this->assertEquals(1, $winners[0]);
        $this->assertEquals(2, $winners[1]);
    }

    public function testEvaluateWinnersTwoPairNoTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(2, 0), new Card(6, 1), new Card(9, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(8, 0), new Card(8, 1), new Card(7, 2), new Card(7, 0), new Card(6, 0), new Card(4, 2), new Card(2, 1)];
        $cards3 = [new Card(8, 0), new Card(8, 1), new Card(6, 2), new Card(6, 0), new Card(3, 0), new Card(10, 2), new Card(9, 2)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(1, count($winners));
        $this->assertEquals(1, $winners[0]);
    }

    public function testEvaluateWinnersTwoPairNoTie2(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(4, 0), new Card(2, 1), new Card(2, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(5, 0), new Card(5, 1), new Card(7, 2), new Card(7, 0), new Card(3, 0), new Card(10, 2), new Card(2, 1)];
        $cards3 = [new Card(2, 0), new Card(8, 1), new Card(6, 2), new Card(6, 0), new Card(3, 0), new Card(10, 2), new Card(9, 2)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(1, count($winners));
        $this->assertEquals(1, $winners[0]);
    }

    public function testEvaluateWinnersTwoPairWithTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(2, 0), new Card(6, 1), new Card(9, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(8, 0), new Card(8, 1), new Card(7, 2), new Card(7, 0), new Card(10, 0), new Card(4, 2), new Card(2, 1)];
        $cards3 = [new Card(8, 0), new Card(8, 1), new Card(7, 2), new Card(7, 0), new Card(10, 0), new Card(4, 2), new Card(9, 2)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(2, count($winners));
        $this->assertEquals(1, $winners[0]);
        $this->assertEquals(2, $winners[1]);
    }

    public function testEvaluateWinnersPairNoTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(2, 0), new Card(6, 1), new Card(9, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(14, 0), new Card(14, 1), new Card(7, 2), new Card(3, 0), new Card(10, 0), new Card(4, 2), new Card(2, 1)];
        $cards3 = [new Card(4, 0), new Card(8, 1), new Card(7, 2), new Card(7, 0), new Card(10, 0), new Card(12, 2), new Card(9, 2)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(1, count($winners));
        $this->assertEquals(1, $winners[0]);
    }

    public function testEvaluateWinnersPairWithTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(2, 0), new Card(6, 1), new Card(9, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(14, 0), new Card(14, 1), new Card(10, 2), new Card(9, 0), new Card(6, 0), new Card(4, 2), new Card(2, 1)];
        $cards3 = [new Card(14, 0), new Card(14, 1), new Card(10, 2), new Card(9, 0), new Card(6, 0), new Card(3, 2), new Card(2, 1)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(2, count($winners));
        $this->assertEquals(1, $winners[0]);
        $this->assertEquals(2, $winners[1]);
    }

    public function testEvaluateWinnersHighCardNoTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(2, 0), new Card(6, 1), new Card(9, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(14, 0), new Card(4, 1), new Card(10, 2), new Card(9, 0), new Card(6, 0), new Card(13, 2), new Card(2, 1)];
        $cards3 = [new Card(2, 0), new Card(8, 1), new Card(6, 2), new Card(5, 0), new Card(3, 0), new Card(11, 2), new Card(13, 1)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(1, count($winners));
        $this->assertEquals(1, $winners[0]);
    }

    public function testEvaluateWinnersHighCardWithTie(): void
    {
        $player1 = new Player("p1", 5000, false, false);
        $player2 = new Player("p2", 5000, false, false);
        $player3 = new Player("p3", 5000, false, false);

        $cards1 = [new Card(4, 2), new Card(2, 0), new Card(6, 1), new Card(9, 3), new Card(13, 1), new Card(5, 2), new Card(7, 3)];
        $cards2 = [new Card(14, 0), new Card(4, 1), new Card(10, 2), new Card(9, 0), new Card(6, 0), new Card(13, 2), new Card(2, 1)];
        $cards3 = [new Card(14, 0), new Card(5, 1), new Card(10, 2), new Card(9, 0), new Card(6, 0), new Card(13, 2), new Card(2, 1)];

        $cards = [$cards1, $cards2, $cards3];
        $players = [$player1, $player2, $player3];

        $winners = $this->evaluateWinnersSetUp($players, $cards);
        $this->assertEquals(2, count($winners));
        $this->assertEquals(1, $winners[0]);
        $this->assertEquals(2, $winners[1]);
    }
}
