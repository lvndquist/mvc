<?php

namespace App\Card;
use App\Card\CardGraphic;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class CardGraphic.
 */
class CardGraphicTest extends TestCase
{

    /**
     * Construct graphic card object
     */
    public function testCreateCardGraphic()
    {
        $card = new CardGraphic(1, 0);
        $this->assertInstanceOf("\App\Card\Card", $card);
        $value = $card->getValue();
        $color = $card->getColor();
        $expValue = 1;
        $expColor = 0;

        $this->assertEquals($expValue, $value);
        $this->assertEquals($expColor, $color);
    }

    /**
     * Convert grapic card to string.
     */
    public function testToString()
    {
        $card = new CardGraphic(1, 0);
        $string = $card->toString();
        $expString = "0-1";

        $this->assertEquals($expString, $string);
    }

}
