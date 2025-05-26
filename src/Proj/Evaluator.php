<?php

namespace App\Proj;

use App\Proj\Card;

/**
 * Class for evaluating texas holdem card hands.
 */
class Evaluator
{
    private array $handRanks = [
        "highCard" => 1,        // highest card
        "pair" => 2,            // 1 pair
        "twoPair" => 3,         // 2 pairs
        "threeOfAKind" => 4,    // 3 of one value
        "straight" => 5,        // 5 cards consecutive value, not same color
        "flush" => 6,           // all cards same color
        "fullHouse" => 7,       // 3 of one value, and 2 of another value
        "fourOfAKind" => 8,     // 4 of one value
        "straightFlush" => 9,   // 5 cards consecutive value, same color
        "royalFlush" => 10      // A, K, Q, J, 10 in same color
    ];

    private array $straightCardValues = [];

    public function evaluateCards(array $cards): array
    {
        //usort($cards, fn($a, $b) => $b->getValue() - $a->getValue());
        $cards = $this->sortCardsByValue($cards);
        $values = array_map(fn($card) => $card->getValue(), $cards);
        $colors = array_map(fn($card) => $card->getColor(), $cards);
        $valueCount = array_count_values($values);
        $colorCount = array_count_values($colors);
        $flush = max($colorCount) >= 5;
        $straight = $this->isStraight($values);

        // Royal flush or Straight flush
        if ($flush && $straight) {
            $straightCards = $this->getStraightCards($cards);
            if ($straightCards[0]->getValue() === 14) {
                return ["Royal Flush", $this->handRanks["royalFlush"], $straightCards];
            }
            $flushCards = $this->getFlushCards($straightCards, $colorCount);
            return ["Straight Flush", $this->handRanks["straightFlush"], $flushCards];
        }

        // Four of a kind
        if (in_array(4, $valueCount)) {
            return ["Four of a kind", $this->handRanks["fourOfAKind"], $this->getFourOfAKindCards($cards, $valueCount)];
        }

        // Full house
        if (in_array(3, $valueCount) && in_array(2, $valueCount)) {
            return ["Full house", $this->handRanks["fullHouse"], $this->getFullHouseCards($cards, $valueCount)];
        }

        // Flush
        if ($flush) {
            return ["Flush", $this->handRanks["flush"], $this->getFlushCards($cards, $colorCount)];
        }

        // Straight
        if ($straight) {
            return ["Straight", $this->handRanks["straight"], $this->getStraightCards($cards)];
        }

    }

    /**
     * Get four of a kind cards.
     */
    public function getFourOfAKindCards(array $cards, array $valueCount): array
    {
        $fourOfAKindCards = [];
        $target = null;

        foreach ($valueCount as $value => $count) {
            if ($count === 4) {
                $target = $value;
                break;
            }
        }

        foreach ($cards as $card) {
            if ($card->getValue() === $target) {
                $fourOfAKindCards[] = $card;
            }
        }

        return $fourOfAKindCards;
    }

    /**
     * Get full house cards.
     */
    public function getFullHouseCards(array $cards, array $valueCount): array
    {
        $fullHouseCards = [];
        $twoCardTarget = null;
        $threeCardTarget = null;

        foreach ($valueCount as $value => $count) {
            if ($count === 3) {
                $threeCardTarget = $value;
            }
            if ($count === 2) {
                $twoCardTarget = $value;
            }
        }

        foreach ($cards as $card) {
            $value = $card->getValue();
            if ($value === $twoCardTarget || $value === $threeCardTarget) {
                $fullHouseCards[] = $card;
            }
        }

        return $fullHouseCards;
    }

    /**
     *  Get flush cards.
     */
    public function getFlushCards(array $cards, array $colorCount): array
    {
        $flushCards = [];
        $target = null;
        foreach ($colorCount as $value => $count) {
            if ($count === 5) {
                $target = $value;
                break;
            }
        }

        foreach ($cards as $card) {
            if ($card->getColor() === $target) {
                $flushCards[] = $card;
            }
        }
        return $flushCards;
    }

    /**
     * Get straight cards.
     */
    public function getStraightCards(array $cards): array
    {
        $straigtCards = [];
        foreach ($cards as $card) {
            if (in_array($card->getValue(), $this->straightCardValues)) {
                $straightCards[] = $card;
            }
        }
        return $this->sortCardsByValue($straightCards);
    }

    /**
     * Evaluate if an array of cards is a stright (5 consecutive cards)
     */
    public function isStraight(array $values): bool
    {
        $uniques = array_unique($values);
        sort($uniques);

        // at this point just skip
        if (count($uniques) < 5) {
            return false;
        }
        for ($j = 0; $j <= count($uniques) - 5; $j++) {
            $straightCardValues = [$uniques[$j]];
            $consecutive = true;
            for ($i = 1; $i < 5; $i++) {
                if ($uniques[$j + $i] !== $uniques[$j] + $i) {
                    $consecutive = false;
                    $straightCardValues = [];
                    break;
                }
                $straightCardValues[] = $uniques[$i + $j];
            }
            if ($consecutive) {
                $this->straightCardValues = $straightCardValues;
                return true;
            }
        }

        return false;
    }

    /**
     * Sort cards in decending order based on value.
     */
    public function sortCardsByValue(array $cards): array
    {
        $sorted = $cards;
        usort($sorted, fn($a, $b) => $b->getValue() - $a->getValue());
        return $sorted;
    }
}
