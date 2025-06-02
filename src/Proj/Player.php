<?php

namespace App\Proj;

use App\Proj\Hand;
use App\Proj\Card;
use Exception;

/**
 * Class representing a player.
 */
class Player
{
    /**
     * Player's hand containing their cards.
     * @var Hand
     */
    private Hand $hand;

    /**
     * Player's name.
     */
    private string $name;

    /**
     * Player's money.
     */
    private int $money;

    /**
     * Player's current bet.
     */
    private int $currentBet;

    /**
     * Player folded.
     */
    private bool $isFolded;

    /**
     * Player all in.
     */
    private bool $allIn;

    /**
     * Player played.
     */
    private bool $played;

    /**
     * Player is computer.
     */
    private bool $computer;

    /**
     * Computer plays smarter
     */
    private bool $smart;

    /**
     * Evaluation of player's combined cards
     *
     * @var array{handString: string, score: int, cards: Card[]}
     */
    private array $evaluation;

    /**
     * Log to keep track of computer decisions.
     * @var array<int, array<string, float|int|list<int>|string|null>>
     */
    private array $computerLog;

    /**
     * Initialize the player object.
     */
    public function __construct(string $name, int $money, bool $computer, bool $smart)
    {
        $this->hand = new Hand();
        $this->name = $name;
        $this->money = $money;
        $this->computer = $computer;
        $this->smart = $smart;
        $this->currentBet = 0;
        $this->isFolded = false;
        $this->allIn = false;
        $this->played = false;
        $this->evaluation = [
            "handString" => "",
            "score" => 0,
            "cards" => []
        ];
        $this->computerLog = [];
    }

    /**
     * Ready players for new round
     */
    public function newRound(): void
    {
        $this->hand = new Hand();
        $this->currentBet = 0;
        $this->isFolded = false;
        $this->allIn = false;
        $this->played = false;
        $this->evaluation = [
            "handString" => "",
            "score" => 0,
            "cards" => []
        ];
        $this->computerLog = [];
    }

    /**
     * Get log of computer plays.
     * @return array<int, array<string,float|int|list<int>|string|null>>
     */
    public function getComputerLog(): array
    {
        return $this->computerLog;
    }

    /**
     * Get smart flag of player.
     */
    public function isSmart(): bool
    {
        return $this->smart;
    }

    /**
     * Gets all cards in the player's hand.
     * @return array<Card>
     */
    public function getHand(): array
    {
        return $this->hand->getCards();
    }

    /**
     * Gets the name of the player.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the money of the player.
     */
    public function getMoney(): int
    {
        return $this->money;
    }

    /**
     * Gets the current bet of the player.
     */
    public function getCurrentBet(): int
    {
        return $this->currentBet;
    }

    /**
     * Has player folded?
     */
    public function isFolded(): bool
    {
        return $this->isFolded;
    }

    /**
     * Has player went all in?
     */
    public function isAllIn(): bool
    {
        return $this->allIn;
    }

    /**
     * Check if player is computer.
     */
    public function isComputer(): bool
    {
        return $this->computer;
    }

    /**
     * Has player played?
     */
    public function hasPlayed(): bool
    {
        return $this->played;
    }

    /**
     * Get evaluation
     * @return array{handString: string, score: int, cards: array<\App\Proj\Card>}
     */
    public function getEvaluation(): array
    {
        return $this->evaluation;
    }

    /**
     * Get the string representation of the best cards that player can play.
     */
    public function getEvaluatedString(): string
    {
        return $this->evaluation["handString"];
    }

    /**
     * Get the score of the best cards that player can play.
     */
    public function getEvaluatedScore(): int
    {
        return $this->evaluation["score"];
    }

    /**
     * Adds a card to the player's hand.
     */
    public function addCard(Card $card): void
    {
        $this->hand->addCard($card);
    }

    /**
     * Set money of player.
     */
    public function setMoney(int $money): void
    {
        $this->money = $money;
    }

    /**
     * Set current bet of player.
     */
    public function setCurrentBet(int $bet): void
    {
        $this->currentBet = $bet;
    }

    /**
     * Set folded flag of player.
     */
    public function setFolded(bool $val): void
    {
        $this->isFolded = $val;
    }

    /**
     * Set all in flag of player.
     */
    public function setAllIn(bool $val): void
    {
        $this->allIn = $val;
    }

    /**
     * Set played flag of player.
     */
    public function setPlayed(bool $val): void
    {
        $this->played = $val;
    }

    /**
     * Set smart flag of player.
     */
    public function setSmart(bool $val): void
    {
        $this->smart = $val;
    }

    /**
     * Set to act as computer, useful in testing
     */
    public function setComputer(bool $val): void
    {
        $this->computer = $val;
    }

    /**
     * Set evaluation.
     * @param string $handString
     * @param int $score
     * @param Card[] $cards
     */
    public function setEvaluation(string $handString, int $score, array $cards): void
    {
        $this->evaluation = [
            "handString" => $handString,
            "score" => $score,
            "cards" => $cards
        ];
    }

    /**
     * Set entry in computer log
     * @param array<string, float|int|list<int>|string|null> $entry
     */
    public function setComputerLog(array $entry): void
    {
        $this->computerLog[] = [
            "name" => $entry["name"],
            "score" => $entry["score"],
            "possibility" => $entry["possibility"],
            "callAmount" => $entry["callAmount"] ?? null,
            "odds" => $entry["odds"] ?? null,
            "randDecision" => $entry["randDecision"] ?? null,
            "phase" => $entry["phase"],
            "takenAction" => $entry["takenAction"] ?? null,
            "extra" => $entry["extra"] ?? null
        ];
    }

    /**
     * Make a bet.
     */
    public function makeBet(int $amount): void
    {
        if ($amount >= $this->money) {
            $this->allIn = true;
            $amount = $this->money;
        }
        $this->money -= $amount;
        $this->currentBet += $amount;
        //$this->played = true;
    }
}
