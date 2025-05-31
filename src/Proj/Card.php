<?php

namespace App\Proj;

/**
 * Class representing a Card.
 */
class Card
{
    /**
     * The card's value.
     */
    protected int $value;

    /**
     * The card's color.
     */
    protected int $color;

    /**
     * The card's color by letter.
     */
    protected string $colorLetter;

    /**
     * Initiate the card object.
     */
    public function __construct(int $value, int $color)
    {
        $this->value = $value;
        $this->color = $color;
        $colorLetters = ["S", "H", "D", "C"];
        $this->colorLetter = $colorLetters[$color];
    }

    /**
     * Get the value of the card.
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Get the color of the card.
     */
    public function getColor(): int
    {
        return $this->color;
    }

    /**
     * Get the letter of the color.
     */
    public function getLetter(): string
    {
        return $this->colorLetter;
    }

    /**
     * Convert card's value and color to a string.
     */
    public function toString(): string
    {
        $colors = ['♠', '♥','♦','♣'];
        $values = [
            2 => "2",
            3 => "3",
            4 => "4",
            5 => "5",
            6 => "6",
            7 => "7",
            8 => "8",
            9 => "9",
            10 => "10",
            11 => "J",
            12 => "Q",
            13 => "K",
            14 => "A"
        ];
        $value = $values[$this->value];
        $color = $colors[$this->color];

        return $color . $value;
    }

}
