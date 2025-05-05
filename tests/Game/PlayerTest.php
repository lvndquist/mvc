<?php

namespace App\Game;
use App\Card\Card;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Player.
 */
class PlayerTest extends TestCase
{

    /**
     * Construct player object without arguments.
     */
    public function testCreatePlayer()
    {
        $player = new Player();
        $this->assertInstanceOf("\App\Game\Player", $player);
        $hand = $player->getHand();
        $score = $player->getScore();
        $expHand = [];
        $expScore = 0;

        $this->assertEquals($expHand, $hand);
        $this->assertEquals($expScore, $score);
    }

    /**
     * Add card to player's hand.
     */
    public function testAddCard()
    {
        $player = new Player();
        $card = new Card(1, 0);
        $player->addCard($card);
        $cards = $player->getHand();
        $value = $cards[0]->getValue();
        $color = $cards[0]->getColor();
        $expValue = 1;
        $expColor = 0;

        $this->assertEquals($expValue, $value);
        $this->assertEquals($expColor, $color);
    }

}
