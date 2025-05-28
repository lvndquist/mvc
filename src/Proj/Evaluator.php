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

        // Three of a kind
        if (in_array(3, $valueCount)) {
            return ["Three of a kind", $this->handRanks["threeOfAKind"], $this->getThreeOfAKindCards($cards, $valueCount)];
        }

        $pairs = $this->getPairs($cards, $valueCount);

        // Two pair
        if (count($pairs) === 4) {
            return ["Two pair", $this->handRanks["twoPair"], $pairs];
        }

        // Pair
        if (count($pairs) === 2) {
            return ["Pair", $this->handRanks["pair"], $pairs];
        }

        // High card
        return ["High card", $this->handRanks["highCard"], [$cards[0]]];
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

        // if there is a tie, a fifth card is needed to determine a winner
        $determining = array_filter($cards, fn($card) => $card->getValue() !== $target);
        $sorted = $this->sortCardsByValue($determining);
        $fourOfAKindCards[] = $sorted[0];

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
     * Get pairs.
     */
    public function getPairs(array $cards, array $valueCount): array
    {
        $pairs = [];
        $targets = [];
        foreach ($valueCount as $value => $count) {
            if ($count === 2) {
                $targets[] = $value;
            }
        }

        foreach ($cards as $card) {
            if (in_array($card->getValue(), $targets)) {
                $pairs[] = $card;
            }
        }
        return $pairs;
    }

    /**
     * Get three of a kind cards.
     */
    public function getThreeOfAKindCards(array $cards, array $valueCount): array
    {
        $threeOfAKindCards = [];
        $target = null;

        foreach ($valueCount as $value => $count) {
            if ($count === 3) {
                $target = $value;
                break;
            }
        }

        foreach ($cards as $card) {
            if ($card->getValue() === $target) {
                $threeOfAKindCards[] = $card;
            }
        }

        return $threeOfAKindCards;
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

    /**
     * Evaluate winner indexes. Multiple winner indexes mean there is a tie
     */
    public function evaluateWinners(array $players): array
    {
        $evals = [];
        foreach ($players as $index => $player) {
            $eval = $player->getEvaluation();
            $score = $eval["score"];
            $cards = $eval["cards"];
            $evals[$index] = [
                "score" => $score,
                "cards" => $cards
            ];
        }

        $max = max(array_column($evals, "score"));
        $indexes = array_keys(array_filter($evals, fn($evl) => $evl["score"] === $max));
        if (count($indexes) === 1 || $max === 10) {
            return [$indexes[0]];
        }
        switch ($max) {
            case 9:
            case 5:
                return $this->straightTie($evals, $indexes);
            case 8:
                return $this->fourOfAKindTie($evals, $indexes);
            default:
                return $indexes;
        }


    }

    /**
     * Straight or straight flush tie, compares the top card to determine winner.
     * Same top card means its a tie.
     */
    public function straightTie($evals, $indexes): array
    {
        foreach ($indexes as $index) {
            $cards = $evals[$index]["cards"];
            $highest[$index] = $cards[0]->getValue();
        }

        $max = max($highest);
        return array_keys(array_filter($highest, fn($val) => $val === $max));
    }

    /**
     * Four of a kind tie, compares the values of the four cards.
     * If those are all the same, it goes to the last card to check what is the highest.
     */
    public function fourOfAKindTie($evals, $indexes): array
    {
        foreach ($indexes as $index) {
            $cards = $evals[$index]["cards"];
            $highest[$index] = $cards[0]->getValue();
        }
        $max = max($highest);

        $highestIndexes = array_keys(array_filter($highest, fn($val) => $val === $max));

        if (count($highestIndexes) <= 1) {
            return $highestIndexes;
        }

        $determining = [];
        foreach($highestIndexes as $index) {
            $cards = $evals[$index]["cards"];
            $determining[$index] = $cards[4]->getValue();
        }

        $maxDetermining = max($determining);
        $highestDeterming = array_keys(array_filter($determining, fn($val) => $val === $maxDetermining));

        return $highestDeterming;
    }

    public function fullHouseTie(): array
    {

    }

    public function flushTie(): array
    {

    }

    public function threeOfAKindTie(): array
    {

    }

    public function twoPairTie(): array
    {

    }

    public function onePairTie(): array
    {

    }

    public function highCardTie(): array
    {

    }

    /**
     * Compare cards in a hand. Used to determine who wins when hands have same score.
     */
    public function compareHands(array $hand1, array $hand2) {
        /*
        return 1;
        return 2;
        return 0;
         */
    }
}
