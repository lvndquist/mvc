<?php

namespace App\Proj;

use App\Proj\Deck;
use App\Proj\Hand;
use App\Proj\Card;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Deck.
 */
class DeckTest extends TestCase
{
    /**
     * Construct card object
     */
    public function testCreateDeck(): void
    {
        $deck = new Deck(false);
        $this->assertInstanceOf("\App\Proj\Deck", $deck);
        $cards = $deck->getCards();
        $this->assertNotEmpty($cards);
    }

    /**
     * Construct card object, graphic
     */
    public function testCreateDeckGraphic(): void
    {
        $deck = new Deck(true);
        $this->assertInstanceOf("\App\Proj\Deck", $deck);
        $cards = $deck->getCards();
        $this->assertNotEmpty($cards);
    }

    public function testShuffle(): void
    {
        $deck = new Deck(false);
        $this->assertInstanceOf("\App\Proj\Deck", $deck);
        $cardsPreShuffle = $deck->getCards();
        $deck->shuffle();
        $cardsPostShuffle = $deck->getCards();
        $diff = false;
        for($i = 0; $i < count($cardsPreShuffle); $i++) {
            $preVal = $cardsPreShuffle[$i]->getValue();
            $preCol = $cardsPreShuffle[$i]->getColor();

            $postVal = $cardsPostShuffle[$i]->getValue();
            $postCol = $cardsPostShuffle[$i]->getColor();

            if ($preVal != $postVal || $preCol != $postCol) {
                $diff = true;
            }
        }

        $this->assertTrue($diff);
    }

    /**
     * Draw card from fresh deck.
     */
    public function testDrawValid(): void
    {
        $deck = new Deck(false);
        $card = $deck->draw();
        $value = $card->getValue();
        $color = $card->getColor();
        $expValue = 14;
        $expColor = 3;
        $this->assertEquals($expValue, $value);
        $this->assertEquals($expColor, $color);
    }

    /**
     * Draw card from empty deck.
     */
    public function testDrawException(): void
    {
        $deck = new Deck(false);
        for ($i = 0; $i < 52; $i++) {
            $deck->draw();
        }
        $this->expectException(Exception::class);
        $deck->draw();
    }

    /**
     * String representation of a deck.
     */
    public function testToString(): void
    {
        $deck = new Deck(false);
        $deckAsString = $deck->toString();
        $cardAsString = $deckAsString[0];
        $expString = "♠2";
        $this->assertEquals($expString, $cardAsString);
    }
}
