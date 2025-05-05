<?php

namespace App\Card;

use App\Card\CardHand;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for class CardHand.
 */
class CardHandTest extends TestCase
{
    /**
     * Construct card hand object
     */
    public function testCreateHand(): void
    {
        $hand = new CardHand();
        $this->assertInstanceOf("\App\Card\CardHand", $hand);
        $cards = $hand->getCards();
        $this->assertEmpty($cards);
    }

    /**
     * Add card to hand.
     */
    public function testAddingCard(): void
    {
        $hand = new CardHand();
        $card = new Card(1, 0);
        $hand->addCard($card);
        $cards = $hand->getCards();
        $value = $cards[0]->getValue();
        $color = $cards[0]->getColor();
        $expValue = 1;
        $expColor = 0;
        $this->assertEquals($expValue, $value);
        $this->assertEquals($expColor, $color);
    }

    /**
     * Add card and then remove it from hand.
     */
    public function testRemovingCard(): void
    {
        $hand = new CardHand();
        $card = new Card(1, 0);
        $hand->addCard($card);
        $hand->removeCard($card);
        $cards = $hand->getCards();
        $this->assertEmpty($cards);
    }

}
