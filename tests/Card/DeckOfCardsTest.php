<?php

namespace App\Card;

use App\Card\DeckOfCards;
use App\Card\CardHand;
use App\Card\Card;
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

    /**
     * Sort deck.
     */
    public function testSort(): void
    {
        $deck = new DeckOfCards(false);
        $deck->shuffle();
        $sortedDeck = $deck->sort();
        $value = $sortedDeck[0]->getValue();
        $color = $sortedDeck[0]->getColor();
        $expValue = 1;
        $expColor = 0;
        $this->assertEquals($expValue, $value);
        $this->assertEquals($expColor, $color);
    }

    /**
     * Draw multiple from deck.
     */
    public function testDrawMultiple(): void
    {
        $deck = new DeckOfCards(false);
        $hand = new CardHand();
        $deck->drawMultiple(2, $hand);
        $handCards= $hand->getCards();
        $value1 = $handCards[0]->getValue();
        $color1 = $handCards[0]->getColor();
        $value2 = $handCards[1]->getValue();
        $color2 = $handCards[1]->getColor();
        $expValue1 = 13;
        $expValue2 = 12;
        $expColor1 = 3;
        $expColor2 = 3;
        $this->assertEquals($expValue1, $value1);
        $this->assertEquals($expColor1, $color1);
        $this->assertEquals($expValue2, $value2);
        $this->assertEquals($expColor2, $color2);
    }

    /**
     * String representation of a deck.
     */
    public function testToString(): void
    {
        $deck = new DeckOfCards(false);
        $deckAsString = $deck->toString();
        $cardAsString = $deckAsString[0];
        $expString = "♠A";
        $this->assertEquals($expString, $cardAsString);
    }
}
