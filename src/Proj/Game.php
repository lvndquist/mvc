<?php

namespace App\Proj;

use App\Proj\CardGraphic;
use App\Proj\Hand;
use App\Proj\Deck;
use App\Proj\Player;
use App\Proj\Evaluator;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Class representing a deck of cards.
 */
class Game
{
    /** @var Player[] */
    private array $players;
    private Hand $dealerCards;
    private Deck $deck;
    private int $pot;
    private int $currPlayerIndex;
    private int $phase;
    private int $currentBet;
    /** @var int[] */
    private array $winners;
    private int $numPlayers = 4;
    private int $smallBlind = 20;
    private int $bigBlind = 40;
    private int $buyBack = 2500;

    /**
     * @var array<int, array{
     *  player: string,
     *  playerIndex: int,
     *  action: string,
     *  amount: int|null,
     *  optional: null|string
     * }>
     */
    private array $playLog;

    private bool $useHelp;
    private bool $useFullHelp;
    private bool $useOpenCards;

    private bool $gameOver;

    private Computer $computer;
    private PlayerActions $actions;

    public function __construct(
        int $startingMoney,
        string $playerName,
        bool $useHelp,
        bool $useFullHelp,
        bool $useOpenCards,
        bool $graphic = true
    ) {
        $this->deck = new Deck($graphic);
        $this->deck->shuffle();
        $this->players = [];
        for ($i = 0; $i < $this->numPlayers; $i++) {
            $name = "Player " . $i + 1;
            $computer = $i !== 0;
            $smart = $i % 2 !== 0;
            if ($i == 0) {
                $name = $playerName;
            }
            $this->players[] = new Player($name, $startingMoney, $computer, $smart);
        }
        $this->gameOver = false;
        $this->pot = 0;
        $this->currPlayerIndex = 1;
        $this->phase = 0;
        $this->currentBet = 0;
        $this->winners = [];
        $this->playLog = [];
        $this->dealerCards = new Hand();
        $this->useHelp = $useHelp;
        $this->useFullHelp = $useFullHelp;
        $this->useOpenCards = $useOpenCards;
        $this->dealToPlayers();
    }

    public function start(Computer $computer, PlayerActions $actions): void
    {
        $this->computer = $computer;
        $this->actions = $actions;
        $this->actions->playerBlind(3, "small blind", $this->smallBlind);
        $this->actions->playerBlind(2, "big blind", $this->bigBlind);
    }

    public function getActions(): PlayerActions
    {
        return $this->actions;
    }

    public function getComputer(): Computer
    {
        return $this->computer;
    }

    /**
     * Deal two cards to each player.
     */
    public function dealToPlayers(): void
    {
        for ($j = 0; $j < 2; $j++) {
            for ($i = 0; $i < $this->numPlayers; $i++) {
                $this->players[$i]->addCard($this->deck->draw());
            }
        }
    }

    /**
     * Update the game state.
     */
    public function updateGameState(): void
    {
        $player = $this->players[$this->currPlayerIndex];
        $this->setEvaluation($player);

        if ($this->isOver()) {
            return;
        }

        // let the computer play
        if ($player->isComputer() && !$player->hasPlayed() && !$player->isFolded()) {
            if ($player->isSmart()) {
                $this->computer->smartComputerPlay($this->currPlayerIndex);
            }

            if (!$player->isSmart()) {
                $this->computer->basicComputerPlay($this->currPlayerIndex);
            }
        }

        // only one player left so handle win
        if ($this->onePlayerLeft()) {
            $this->handleWin();
            return;
        }

        // go to next phase
        if ($this->allPlayed()) {
            $this->nextPhase();
            return;
        }

        // check if valid for playing otherwise go to next player
        if ($player->isFolded() || $player->isAllIn() || $player->getMoney() === 0) {
            $this->nextPlayer();
            return;
        }

        // if human player hasnt played, return
        if (!$player->hasPlayed() && !$player->isComputer()) {
            return;
        }

        $this->nextPlayer();
        return;
    }

    /**
     * Set evaluation of players
     */
    public function setEvaluation(Player $player): void
    {
        $allCards = array_merge($player->getHand(), $this->getDealerCards());
        $evaluator = new Evaluator();
        $res = $evaluator->evaluateCards($allCards);
        $handString = $res["handString"];
        $score = $res["score"];
        $cards = $res["cards"];
        $player->setEvaluation($handString, $score, $cards);
    }

    /**
     * Go to next phase.
     */
    public function nextPhase(): void
    {
        $this->phase++;
        $phase = $this->phase;
        // flop: dealer puts 3 cards
        if ($phase === 1) {
            $this->dealerCards->addCard($this->deck->draw());
            $this->dealerCards->addCard($this->deck->draw());
            $this->dealerCards->addCard($this->deck->draw());
            $this->advancePlayers();
            return;
        }

        if ($phase === 4) {
            $this->checkBets();
            $this->handleWin();
            return;
        }

        $this->dealerCards->addCard($this->deck->draw());
        $this->advancePlayers();
        return;
    }

    /**
     * Set all players as not played and go to next player
     */
    public function advancePlayers(): void
    {
        foreach ($this->players as $player) {
            $player->setPlayed(false);
        }
        $this->nextPlayer();
    }

    /**
     * In final round, if there are new bets the players who havent matched those bets
     * need to be able to play again
     */
    public function checkBets(): void
    {
        $players = $this->players;
        foreach ($players as $index => $player) {
            if ($player->isComputer() && !$player->isFolded() && !$this->canCheck($index)) {
                if ($player->isSmart()) {
                    $this->computer->smartComputerPlay($index);
                }
                if (!$player->isSmart()) {
                    $this->computer->basicComputerPlay($index);
                }
            }
        }
    }

    /**
     * Update the currPlayerIndex, ignoring players who have folded or are all in.
     */
    public function nextPlayer(): void
    {
        $nextIndex = $this->currPlayerIndex;
        $count = 0;

        while ($count < $this->numPlayers) {
            $nextIndex = ($nextIndex - 1 + $this->numPlayers) % $this->numPlayers;
            $player = $this->players[$nextIndex];

            if (!$player->isFolded() && !$player->isAllIn()) {
                $this->currPlayerIndex = $nextIndex;
                return;
                //break;
            }
            $count++;
        }

        // no one can play...
        $this->nextPhase();
    }

    /**
     * Check if all players played.
     */
    public function allPlayed(): bool
    {
        foreach ($this->players as $player) {
            if ($player->isFolded() || $player->isAllIn() || $player->getMoney() == 0) {
                continue;
            }
            if (!$player->hasPlayed()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if there is only one player left.
     */
    public function onePlayerLeft(): bool
    {
        $count = 0;
        foreach ($this->players as $player) {
            if (!$player->isFolded() && ($player->getMoney() > 0 || $player->isAllIn())) {
                $count += 1;
            }
        }
        return ($count === 1);
    }

    /**
     * Handle a win.
     */
    public function handleWin(): void
    {
        $players =  $this->players;
        $validPlayers = array_filter($players, fn ($player) => !$player->isFolded());

        $evaluator = new Evaluator();
        $this->winners = $evaluator->evaluateWinners($validPlayers);
        $winners = $this->winners;
        foreach ($winners as $winner) {
            $this->writeToLog($winner, "win", intdiv($this->pot, count($winners)));
        }
        $this->gameOver = true;
    }

    public function nextRound(): void
    {
        $players =  $this->players;
        $deck = $this->deck;
        $numPlayers = $this->numPlayers;
        $this->playLog = [];

        if ($deck->size() < 5 + $numPlayers * 2) {
            $this->deck = new Deck(true);
            $this->deck->shuffle();
        }

        foreach ($players as $index => $player) {
            $player->newRound();
            if (in_array($index, $this->winners)) {
                $player->setMoney($player->getMoney() + intdiv($this->pot, count($this->winners)));
            } elseif ($player->getMoney() < $this->bigBlind) {
                // buy back in
                $player->setMoney($this->buyBack);
                $this->writeToLog($index, "buy in", $this->buyBack);
            }
        }
        //$this->deck = new Deck(true);
        //$this->deck->shuffle();
        $this->pot = 0;
        $this->currPlayerIndex = 1;
        $this->phase = 0;
        $this->currentBet = 0;
        $this->winners = [];
        $this->dealerCards = new Hand();

        $this->dealToPlayers();
        $this->actions->playerBlind(3, "small blind", $this->smallBlind);
        $this->actions->playerBlind(2, "big blind", $this->bigBlind);
        $this->gameOver = false;
    }

    /**
     * Can a player check?
     */
    public function canCheck(int $playerIndex): bool
    {
        $player = $this->players[$playerIndex];
        return ($player->getCurrentBet() === $this->currentBet);
    }

    /**
     * Write to log.
     */
    public function writeToLog(int $playerIndex, string $action, ?int $amount = null, ?string $optional = null): void
    {
        $entry = [
            "player" => $this->players[$playerIndex]->getName(),
            "playerIndex" => $playerIndex,
            "action" => $action,
            "amount" => $amount,
            "optional" => $optional
        ];
        $this->playLog[] = $entry;
    }

    /**
     * Get log.
     * @return array<int, array{
     *  player: string,
     *  playerIndex: int,
     *  action: string,
     *  amount: int|null,
     *  optional: null|string
     * }>
     */
    public function getLog(): array
    {
        return $this->playLog;
    }

    /**
     * Get players.
     * @return Player[]
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    /**
     * Get player cards.
     * @return Card[]
     */
    public function getPlayerCards(int $playerIndex): array
    {
        return $this->players[$playerIndex]->getHand();
    }

    /**
     * Get dealer cards.
     * @return Card[]
     */
    public function getDealerCards(): array
    {
        return $this->dealerCards->getCards();
    }

    /**
     * Get deck.
     */
    public function getDeck(): Deck
    {
        return $this->deck;
    }

    /**
     * Get pot.
     */
    public function getPot(): int
    {
        return $this->pot;
    }

    /**
     * Get current player index.
     */
    public function getCurrPlayerIndex(): int
    {
        return $this->currPlayerIndex;
    }

    /**
     * Get phase.
     */
    public function getPhase(): int
    {
        return $this->phase;
    }

    /**
     * Get current bet.
     */
    public function getCurrentBet(): int
    {
        return $this->currentBet;
    }

    /**
     * Get number of players.
     */
    public function getNumPlayers(): int
    {
        return $this->numPlayers;
    }

    /**
     * Check if the game is over.
     */
    public function isOver(): bool
    {
        return $this->gameOver;
        /*
        if (empty($this->winners)) {
            return false;
        }
        return true;
         */
    }

    /**
     * Get winning player
     * @return int[]
     */
    public function getWinner(): array
    {
        return $this->winners;
    }

    /**
     * Get useHelpflag.
     */
    public function getUseHelp(): bool
    {
        return $this->useHelp;
    }

    /**
     * Get useFullHelp flag.
     */
    public function getUseFullHelp(): bool
    {
        return $this->useFullHelp;
    }

    /**
     * Get useOpenCards flag.
     */
    public function getUseOpenCards(): bool
    {
        return $this->useOpenCards;
    }

    /**
     * Set useHelp flag.
     */
    public function setUseHelp(bool $val): void
    {
        $this->useHelp = $val;
    }

    /**
     * Set useFullHelp flag.
     */
    public function setUseFullHelp(bool $val): void
    {
        $this->useFullHelp = $val;
    }

    /**
     * Set useOpenCards flag.
     */
    public function setUseOpenCards(bool $val): void
    {
        $this->useOpenCards = $val;
    }

    public function setCurrentBet(int $val): void
    {
        $this->currentBet = $val;
    }

    public function setPot(int $val): void
    {
        $this->pot = $val;
    }
}
