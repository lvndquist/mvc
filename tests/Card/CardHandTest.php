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
    public function testCreateHand()
    {
        $hand = new CardHand();
        $this->assertInstanceOf("\App\Card\CardHand", $hand);
        $cards = $hand->getCards();
        $this->assertEmpty($cards);
    }

    /**
     * Add card to hand.
     */
    public function testAddingCard()
    {
        $hand = new CardHand();
        $card = new Card(1, 0);
        $hand->addCard($card);
        $cards = $hand->getCards();
        $value = $cards[0]->getValue();
        $color = $cards[0]->getColor();
        $expValue = 1;
        $expColor = 0;
        $this->assertEquals($expValue, 1);
        $this->assertEquals($expColor, 0);
    }

    /**
     * Add card and then remove it from hand.
     */
    public function testRemovingCard()
    {
        $hand = new CardHand();
        $card = new Card(1, 0);
        $hand->addCard($card);
        $hand->removeCard($card);
        $cards = $hand->getCards();
        $this->assertEmpty($cards);
    }

}
