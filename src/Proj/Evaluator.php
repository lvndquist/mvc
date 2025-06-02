<?php

namespace App\Proj;

use App\Proj\Card;

/**
 * Class for evaluating texas holdem card hands.
 */
class Evaluator
{
    /** @var array<string, int> */
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

    /** @var array<int> */
    private array $straightCardValues = [];

    /**
     * Evaluate cards in a hand, and get the corresponding score.
     * @param Card[] $cards
     * @return array{
     *  handString: string,
     *  score: int,
     *  cards: Card[]
     * }
     */
    public function evaluateCards(array $cards): array
    {
        //usort($cards, fn($a, $b) => $b->getValue() - $a->getValue());
        $cards = $this->sortCardsByValue($cards);
        $values = array_map(fn ($card) => $card->getValue(), $cards);
        $colors = array_map(fn ($card) => $card->getColor(), $cards);
        $valueCount = array_count_values($values);
        $colorCount = array_count_values($colors);
        $flush = !empty($colorCount) && max($colorCount) >= 5;
        $straight = $this->isStraight($values);

        // Royal flush or Straight flush
        if ($flush && $straight) {
            $straightCards = $this->getStraightCards($cards);
            if ($straightCards[0]->getValue() === 14) {
                return $this->wrapEval(["Royal Flush", $this->handRanks["royalFlush"], $straightCards]);
            }
            $flushCards = $this->getFlushCards($straightCards, $colorCount);
            return $this->wrapEval(["Straight Flush", $this->handRanks["straightFlush"], $flushCards]);
        }

        // Four of a kind
        if (in_array(4, $valueCount)) {
            return $this->wrapEval(["Four of a kind", $this->handRanks["fourOfAKind"], $this->getFourOfAKindCards($cards, $valueCount)]);
        }

        // Full house
        if (in_array(3, $valueCount) && in_array(2, $valueCount)) {
            return $this->wrapEval(["Full house", $this->handRanks["fullHouse"], $this->getFullHouseCards($cards, $valueCount)]);
        }

        // Flush
        if ($flush) {
            return $this->wrapEval(["Flush", $this->handRanks["flush"], $this->getFlushCards($cards, $colorCount)]);
        }

        // Straight
        if ($straight) {
            return $this->wrapEval(["Straight", $this->handRanks["straight"], $this->getStraightCards($cards)]);
        }

        // Three of a kind
        if (in_array(3, $valueCount)) {
            return $this->wrapEval(["Three of a kind", $this->handRanks["threeOfAKind"], $this->getThreeOfAKindCards($cards, $valueCount)]);
        }

        $pairs = $this->getPairs($cards, $valueCount);

        // Two pair
        if ($pairs["numPairs"] === 2) {
            return $this->wrapEval(["Two pair", $this->handRanks["twoPair"], $pairs["pairs"]]);
        }

        // Pair
        if ($pairs["numPairs"] === 1) {
            return $this->wrapEval(["Pair", $this->handRanks["pair"], $pairs["pairs"]]);
        }

        // High card
        return $this->wrapEval(["High card", $this->handRanks["highCard"], $this->getHighCards($cards)]);
    }

    /**
     * Return formatting for evaluation
     * @param array{0: string, 1: int, 2: Card[]} $eval
     * @return array{
     *  handString: string,
     *  score: int,
     *  cards: Card[]
     * }
     */
    public function wrapEval(array $eval): array
    {
        return [
            "handString" => $eval[0],
            "score" => $eval[1],
            "cards" => $eval[2]
        ];
    }

    /**
     * Get four of a kind cards.
     * @param Card[] $cards
     * @param int[] $valueCount
     * @return Card[]
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
        $determining = array_filter($cards, fn ($card) => $card->getValue() !== $target);
        $sorted = $this->sortCardsByValue($determining);
        $fourOfAKindCards[] = $sorted[0];

        return $fourOfAKindCards;
    }

    /**
     * Get full house cards. Sorted by three part first then two part.
     * @param Card[] $cards
     * @param int[] $valueCount
     * @return Card[]
     */
    public function getFullHouseCards(array $cards, array $valueCount): array
    {
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

        $threeCards = [];
        $twoCards = [];

        foreach ($cards as $card) {
            $value = $card->getValue();
            if ($value === $threeCardTarget) {
                $threeCards[] = $card;
            } elseif ($value === $twoCardTarget) {
                $twoCards[] = $card;
            }
        }

        return array_merge($threeCards, $twoCards);
    }

    /**
     *  Get flush cards. Sorted by highest to lowest
     * @param Card[] $cards
     * @param int[] $colorCount
     * @return Card[]
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

        return $this->sortCardsByValue($flushCards);
    }

    /**
     * Get straight cards. Sorted from highest to lowest
     *
     * @param Card[] $cards
     * @return Card[]
     */
    public function getStraightCards(array $cards): array
    {
        $straightCards = [];
        foreach ($cards as $card) {
            if (in_array($card->getValue(), $this->straightCardValues)) {
                $straightCards[] = $card;
            }
        }
        return $this->sortCardsByValue($straightCards);
    }

    /**
     * Evaluate if an array of cards is a stright (5 consecutive cards)
     * @param int[] $values
     * @return bool
     */
    public function isStraight(array $values): bool
    {
        $uniques = array_unique($values);
        sort($uniques);

        // at this point just skip
        if (count($uniques) < 5) {
            return false;
        }
        $count = count($uniques);
        for ($j = 0; $j <= $count - 5; $j++) {
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
     * Get pairs. Includes the other highest cards that are not pairs,
     * for two pairs the other highest card is also included.
     * for one pair the three other highest cards are included.
     * @param Card[] $cards
     * @param int[] $valueCount
     * @return array{
     *  pairs: Card[],
     *  numPairs: int
     * }
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

        $otherCards = [];
        rsort($targets);
        $targets = array_slice($targets, 0, 2);

        foreach ($cards as $card) {
            if (in_array($card->getValue(), $targets)) {
                $pairs[] = $card;
                continue;
            }
            $otherCards[] = $card;
        }
        $sorted = $this->sortCardsByValue($otherCards);
        $include = array_slice($sorted, 0, 5 - count($pairs));
        $pairs = $this->sortCardsByValue($pairs);
        $numPairs = 0;
        if (count($pairs) === 4) {
            $numPairs = 2;
        } elseif (count($pairs) === 2) {
            $numPairs = 1;
        }

        return [
            "pairs" => array_merge($pairs, $include),
            "numPairs" => $numPairs
        ];
        //return [array_merge($pairs, $include), $numPairs];
    }

    /**
     * Get three of a kind cards. Also includes the 2 highest other cards
     * @param Card[] $cards
     * @param int[] $valueCount
     * @return Card[]
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

        $notThreeOfAKind = [];
        foreach ($cards as $card) {
            if ($card->getValue() === $target) {
                $threeOfAKindCards[] = $card;
                continue;
            }
            $notThreeOfAKind[] = $card;
        }

        $ordered = $this->sortCardsByValue($notThreeOfAKind);
        $highest = array_slice($ordered, 0, 2);

        return array_merge($threeOfAKindCards, $highest);
    }

    /**
     * Get 5 highest cards.
     * @param Card[] $cards
     * @return Card[]
     */
    public function getHighCards(array $cards): array
    {
        $sorted = $this->sortCardsByValue($cards);
        return array_slice($sorted, 0, 5);
    }

    /**
     * Sort cards in decending order based on value.
     * @param Card[] $cards
     * @return Card[]
     */
    public function sortCardsByValue(array $cards): array
    {
        $sorted = $cards;
        usort($sorted, fn ($val1, $val2) => $val2->getValue() - $val1->getValue());
        return $sorted;
    }

    /**
     * Evaluate winner indexes. Multiple winner indexes mean there is a tie.
     * Cards need to be sorted based on their hands rank and in decending value.
     * Each player has a hand of 5 cards, if the hand isnt using 5 cards as with
     * three of a kind etc, the rest will be the highest cards available.
     * @param Player[] $players
     * @return int[]
     */
    public function evaluateWinners(array $players): array
    {
        /** @var array<int, array{score: int, cards: array<\App\Proj\Card>}> $evals */
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

        $max = !empty($evals) ? max(array_column($evals, "score")) : 0;
        $indexes = array_keys(array_filter($evals, fn ($evl) => $evl["score"] === $max));
        if (count($indexes) === 1 || $max === 10) {
            return $indexes;
        }
        switch ($max) {
            case 9:
            case 5:
                // straight and straight flush
                // first card, rest are lower
                return $this->twoPartTie($evals, $indexes, 0, 1, 0);
            case 8:
                // four of a kind
                // first the four cards, then the fifth.
                return $this->twoPartTie($evals, $indexes, 0, 4, 1);
            case 7:
                // full house
                // first three cards, then the fourth (fifth is the same)
                return $this->twoPartTie($evals, $indexes, 0, 3, 1);
            case 6:
                // flush
                // first card, then next and so on
                return $this->twoPartTie($evals, $indexes, 0, 0, 5);
                //return $this->compareAllTie($evals, $indexes);
            case 4:
                // three of a kind
                // first three cards, then the last two.
                return $this->twoPartTie($evals, $indexes, 0, 3, 2);
            case 3:
                // two pair
                // first pair, then second pair, last the fifth card
                return $this->twoPairTie($evals, $indexes);
            case 2:
                // one pair
                // first the pair, then the 3 rest.
                return $this->twoPartTie($evals, $indexes, 0, 2, 3);
            case 1:
                // highest card
                // first card then the rest.
                return $this->twoPartTie($evals, $indexes, 0, 0, 5);
            default:
                return [];
        }

    }

    /**
     * Compares card at some index.
     *
     * @param array<array{score: mixed, cards: Card[]}> $evals
     * @param int[] $indexes
     * @return int[]
     */
    public function onePartTie(array $evals, array $indexes, int $start): array
    {
        $highest = [];
        foreach ($indexes as $index) {
            $cards = $evals[$index]["cards"];
            $highest[$index] = $cards[$start]->getValue();
        }

        $max = !empty($highest) ? max($highest) : 0;
        return array_keys(array_filter($highest, fn ($val) => $val === $max));
    }

    /**
     * Use in determining a tie that has two parts like:
     * full house: first three cards then the pair.
     * flush: compare highest then next ...
     * three of a kind: check first three then remaining 2.
     * one pair: check pair then the rest
     * highest card: check first card then the rest.
     *
     * @param array<array{score: mixed, cards: array<\App\Proj\Card>}> $evals
     * @param int[] $indexes
     * @return int[]
     */
    public function twoPartTie(array $evals, array $indexes, int $firstPart, int $secondPart, int $secondPartLen): array
    {
        /*
        $start = [];
        foreach ($indexes as $index) {
            $cards = $evals[$index]["cards"];
            $start[$index] = $cards[$firstPart]->getValue();
        }
        $max = max($start);

        $remaining = array_keys(array_filter($start, fn($val) => $val === $max));
        */
        $remaining = $this->onePartTie($evals, $indexes, $firstPart);

        if (count($remaining) <= 1) {
            return $remaining;
        }

        for ($i = 0; $i < $secondPartLen; $i++) {
            $determining = [];
            foreach ($remaining as $index) {
                $determining[$index] = $evals[$index]["cards"][$secondPart + $i]->getValue();
            }

            $max = !empty($determining) ? max($determining) : 0;
            $remaining = array_keys(array_filter($determining, fn ($val) => $val === $max));

            /*
            if (count($remaining) <= 1) {
                return $remaining;
            } */
        }

        return $remaining;
    }

    /**
     * Two pairs tie
     * @param array<array{score: mixed, cards: Card[]}> $evals
     * @param int[] $indexes
     * @return int[]
     */
    public function twoPairTie(array $evals, array $indexes): array
    {
        $firstPair = $this->onePartTie($evals, $indexes, 0);
        if (count($firstPair) === 1) {
            return $firstPair;
        }

        $secondPair = $this->onePartTie($evals, $indexes, 2);
        if (count($secondPair) === 1) {
            return $secondPair;
        }
        $lastCard = $this->onePartTie($evals, $indexes, 4);
        return $lastCard;

    }
}
