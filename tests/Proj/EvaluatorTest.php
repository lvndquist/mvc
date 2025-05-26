<?php

namespace App\Proj;

use App\Proj\Card;
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

        $resString = $res[0];
        $resScore = $res[1];
        $resCards = $res[2];

        $expResString = "Royal Flush";
        $expResScore = 10;

        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals(14 - $i, $resCards[$i]->getValue());
            $this->assertEquals(0, $resCards[$i]->getColor());
        }
        $this->assertEquals($expResString, $resString);
        $this->assertEquals($expResScore, $resScore);
    }

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

        $resString = $res[0];
        $resScore = $res[1];
        $resCards = $res[2];

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
     * Construct card object
    public function testFourOfAKind(): void
    {

    }

    public function testFullHouse(): void
    {

    }


    public function testFlush(): void
    {

    }


    public function testStraight(): void
    {

    }

    public function testCreateCard(): void
    {
        $card = new Card(1, 0);
        $this->assertInstanceOf("\App\Card\Card", $card);
        $value = $card->getValue();
        $color = $card->getColor();
        $expValue = 1;
        $expColor = 0;

        $this->assertEquals($expValue, $value);
        $this->assertEquals($expColor, $color);
    }

    /**
     * Convert card to string.
    public function testToString(): void
    {
        $card = new Card(1, 0);
        $string = $card->toString();
        $expString = "♠A";

        $this->assertEquals($expString, $string);
    }
     */
}
