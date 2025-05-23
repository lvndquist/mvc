<?php

namespace App\Proj;

/**
 * Class representing a graphic card.
 */
class CardGraphic extends Card
{
    /**
     * The string representation of the card.
     */
    private string $representation;

    /**
     * Initiate the grapic card object.
     */
    public function __construct(int $value, int $color)
    {
        parent::__construct($value, $color);
        $this->representation = $color . "-" . $value;
    }

    /**
     * Get the string representation of the card.
     */
    public function toString(): string
    {
        return $this->representation;
    }
}
