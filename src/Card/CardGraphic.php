<?php

namespace App\Card;

class CardGraphic extends Card
{

    private string $representation;

    public function __construct(int $value, int $color)
    {
        parent::__construct($value, $color);
        $this->representation = $color . "-" . $value;
    }

    public function toString(): string
    {
        return $this->representation;
    }
}
