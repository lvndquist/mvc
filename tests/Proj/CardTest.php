<?php

namespace App\Proj;

use App\Proj\Card;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Card.
 */
class CardTest extends TestCase
{
    /**
     * Construct card object
     */
    public function testCreateCard(): void
    {
        $card = new Card(14, 0);
        $this->assertInstanceOf("\App\Proj\Card", $card);
        $value = $card->getValue();
        $color = $card->getColor();
        $letter = $card->getLetter();
        $expValue = 14;
        $expColor = 0;
        $expLetter = "S";

        $this->assertEquals($expValue, $value);
        $this->assertEquals($expColor, $color);
        $this->assertEquals($expLetter, $letter);
    }

    /**
     * Convert card to string.
     */
    public function testToString(): void
    {
        $card = new Card(14, 0);
        $string = $card->toString();
        $expString = "♠A";

        $this->assertEquals($expString, $string);
    }

}
