<?php

namespace App\Card;

class Card
{
    protected int $value;
    protected int $color;

    public function __construct(int $value, int $color)
    {
        $this->value = $value;
        $this->color = $color;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getColor(): int
    {
        return $this->color;
    }

    public function toString(): string
    {
        $colors = ['♠', '♥','♦','♣'];
        $values = [
            1 => "A",
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
            13 => "K"
        ];
        $value = $values[$this->value];
        $color = $colors[$this->color];

        return $color . $value;
    }
}

