<?php

namespace App\Proj;

use App\Proj\CardGraphic;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for class CardGraphic.
 */
class CardGraphicTest extends TestCase
{
    /**
     * Construct graphic card object
     */
    public function testCreateCardGraphic(): void
    {
        $card = new CardGraphic(14, 0);
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
     * Convert grapic card to string.
     */
    public function testToString(): void
    {
        $card = new CardGraphic(14, 0);
        $string = $card->toString();
        $expString = "0-14";

        $this->assertEquals($expString, $string);
    }

}
