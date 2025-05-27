<?php

namespace App\Proj;

use App\Proj\Card;
use App\Proj\Hand;
use App\Proj\Game;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Card.
 */
class GameTest extends TestCase
{

    /**
     * Test a hand with a high card.
     */
    public function testHighCard(): void
    {
        $game = new Game(50000, "test", true, true, true);

        $playerHand = new Hand();
        $playerHand->addCard(new Card(11, 1));
        $playerHand->addCard(new Card(11, 2));

        $cards[] = new Card(8, 2);
        $cards[] = new Card(2, 1);
        $cards[] = new Card(3, 1);
        $cards[] = new Card(5, 3);
        $cards[] = new Card(9, 2);
        $res = $evaluator->evaluateCards($cards);

        $resString = $res[0];
        $resScore = $res[1];
        $resCards = $res[2];

        $expResString = "High card";
        $expResScore = 1;

        $this->assertEquals(14, $resCards->getValue());
        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }
}
