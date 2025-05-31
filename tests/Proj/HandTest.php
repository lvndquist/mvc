<?php

namespace App\Proj;

use App\Proj\Hand;
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
        $hand = new Hand();
        $this->assertInstanceOf("\App\Proj\Hand", $hand);
        $cards = $hand->getCards();
        $this->assertEmpty($cards);
    }

    /**
     * Add card to hand.
     */
    public function testAddingCard(): void
    {
        $hand = new Hand();
        $card = new Card(14, 0);
        $hand->addCard($card);
        $cards = $hand->getCards();
        $value = $cards[0]->getValue();
        $color = $cards[0]->getColor();
        $expValue = 14;
        $expColor = 0;
        $this->assertEquals($expValue, $value);
        $this->assertEquals($expColor, $color);
    }

    /**
     * Add card and then remove it from hand.
     */
    public function testRemovingCard(): void
    {
        $hand = new Hand();
        $card = new Card(14, 0);
        $hand->addCard($card);
        $hand->removeCard($card);
        $cards = $hand->getCards();
        $this->assertEmpty($cards);
    }


    /**
     * Convert hand to string.
     */
    public function testToString(): void
    {
        $card1 = new Card(14, 0);
        $card2 = new Card(2, 0);
        $hand = new Hand();
        $hand->addCard($card1);
        $hand->addCard($card2);
        $string = $hand->toString();
        $expArray = ["♠A", "♠2"];

        $this->assertEquals($expArray, $string);
    }

}
