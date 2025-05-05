<?php

namespace App\Card;

use App\Card\DeckOfCards;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for class DeckOfCards.
 */
class DeckOfCardsTest extends TestCase
{
    /**
     * Construct card object
     */
    public function testCreateDeckOfCards(): void
    {
        $deck = new DeckOfCards(false);
        $this->assertInstanceOf("\App\Card\DeckOfCards", $deck);
        $cards = $deck->getCards();
        $this->assertNotEmpty($cards);
    }

    /**
     * Draw card from fresh deck.
     */
    public function testDrawValid(): void
    {
        $deck = new DeckOfCards(false);
        $card = $deck->draw();
        $value = $card->getValue();
        $color = $card->getColor();
        $expValue = 13;
        $expColor = 3;
        $this->assertEquals($expValue, $value);
        $this->assertEquals($expColor, $color);
    }

    /**
     * Draw card from empty deck.
     */
    public function testDrawException(): void
    {
        $deck = new DeckOfCards(false);
        for ($i = 0; $i < 52; $i++) {
            $deck->draw();
        }
        $this->expectException(Exception::class);
        $deck->draw();
    }
}
