<?php

namespace App\Proj;

/**
 * Class for evaluating texas holdem card hands.
 */
class Evaluator
{
    private static array $handRanks = [
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

    public function evaluateCards(array $cards): array
    {

    }
}
