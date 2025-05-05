<?php

namespace App\Card;
use App\Card\Card;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Card.
 */
class CardTest extends TestCase
{

    /**
     * Construct card object
     */
    public function testCreateCard()
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
     */
    public function testToString()
    {
        $card = new Card(1, 0);
        $string = $card->toString();
        $expString = "♠A";

        $this->assertEquals($expString, $string);
    }

}
