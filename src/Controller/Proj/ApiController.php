<?php

namespace App\Controller\Proj;

use App\Proj\Game;
use App\Proj\Hand;
use App\Proj\Computer;
use App\Proj\PlayerActions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ApiController extends AbstractController
{
    #[Route("proj/api", name: "proj_api")]
    public function api(): Response
    {
        return $this->render('proj/api.html.twig');
    }

    public function gameResponse(SessionInterface $session, string $action): Response
    {
        /** @var Game */
        $game = $session->get("apiGame");

        $players = $game->getPlayers();
        $self = $players[0];

        $dealerCards = $game->getDealerCards();
        $selfCards = $self->getHand();
        $selfBet = $self->getCurrentBet();
        $selfMoney = $self->getMoney();
        $gameBet = $game->getCurrentBet();
        $gamePot = $game->getPot();
        $gameLog = $game->getLog();
        $playerIndex = $game->getCurrPlayerIndex();
        $winners = $game->getWinner();

        $playersInfo = [];
        foreach ($players as $index => $player) {
            $playersInfo[$index]["name"] = $player->getName();
            $playersInfo[$index]["money"] = $player->getMoney();
            $playersInfo[$index]["currentBet"] = $player->getCurrentBet();
            $playersInfo[$index]["isFolded"] = $player->isFolded();
            $playersInfo[$index]["isAllIn"] = $player->isAllIn();
            $playersInfo[$index]["isComputer"] = $player->isComputer();
            $playersInfo[$index]["isSmart"] = $player->isComputer();
        }

        $selfHand = new Hand();
        foreach ($selfCards as $card) {
            $selfHand->addCard($card);
        }

        $dealerHand = new Hand();
        foreach ($dealerCards as $card) {
            $dealerHand->addCard($card);
        }

        $response = new JsonResponse(
            [
            "action" => $action,
            "currentPlayerIndex" => $playerIndex,
            "winnerIndexes" => $winners,
            "communityCards" => $dealerHand->toString(),
            "playerCards" => $selfHand->toString(),
            "selfBet" => $selfBet,
            "selfMoney" => $selfMoney,
            "playersInfo" => $playersInfo,
            "gameBet" => $gameBet,
            "gamePot" => $gamePot,
            "gameLog" => array_reverse($gameLog)
            ]
        );

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    public function gamePlay(SessionInterface $session, string $action): Response
    {
        /** @var Game|null */
        $game = $session->get("apiGame");

        if (!$game) {
            return new JsonResponse(["Failed... no game found"]);
        }

        if ($game->getPlayers()[0]->isFolded()) {
            while (!$game->isOver()) {
                $game->updateGameState();
            }

            $session->set("apiGame", $game);
            return $this->gameResponse($session, $action);
        }

        $game->updateGameState();
        while ($game->getCurrPlayerIndex() !== 0) {
            $game->updateGameState();
        }

        $session->set("apiGame", $game);
        return $this->gameResponse($session, $action);
    }

    #[Route("proj/api/start-game", name: "api_start_game", methods: ["POST"])]
    public function startGame(SessionInterface $session): Response
    {
        $name = "self";
        $startingMoney = 5000;

        $game = new Game($startingMoney, $name, false, false, false, false);
        $actions = new PlayerActions($game);
        $computer = new Computer($game, $actions);
        $game->start($computer, $actions);

        $game->updateGameState();
        $session->set("apiGame", $game);

        $response = $this->gameResponse($session, "start");

        return $response;
    }

    #[Route("proj/api/get-game", name: "get_game")]
    public function getGame(SessionInterface $session): Response
    {
        return $this->gameResponse($session, "get game");
    }

    #[Route("proj/api/best-hands", name: "best_hands")]
    public function getBestHands(SessionInterface $session): Response
    {
        /** @var Game|null */
        $game = $session->get("apiGame");

        if (!$game) {
            return new JsonResponse(["Failed... no game found"]);
        }
        $players = $game->getPlayers();
        $evaluation = [];
        foreach ($players as $player) {
            $game->setEvaluation($player);
            $playerEvaluation = $player->getEvaluation();
            $handString = $playerEvaluation["handString"];
            $score = $playerEvaluation["score"];
            $cards = $playerEvaluation["cards"];
            $cardsToString = [];
            foreach ($cards as $card) {
                $cardsToString[] = $card->toString();
            }
            $evaluation[] = [
                "name" => $player->getName(),
                "score" => $score,
                "handString" => $handString,
                "cards" => $cardsToString
            ];
        }
        $response = new JsonResponse($evaluation);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;

    }

    #[Route("proj/api/raise", name: "raise", methods: ["POST"])]
    public function playRaise(SessionInterface $session, Request $request): Response
    {
        $amount = (int) $request->request->get('amount');
        /** @var Game|null */
        $game = $session->get("apiGame");

        if (!$game) {
            return new JsonResponse(["Could not raise... no game found"]);
        }

        $folded = $game->getPlayers()[0]->isFolded();
        $actions = $game->getActions();

        if (!$game->isOver() && !$folded) {
            $actions->playerRaise(0, $amount);
            return $this->gamePlay($session, "raise");
        }

        $response = new JsonResponse(["Could not raise... round has finished"]);
        if ($folded) {
            $response = new JsonResponse(["You folded and cant continue"]);
        }

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    #[Route("proj/api/fold", name: "fold", methods: ["POST"])]
    public function playFold(SessionInterface $session): Response
    {
        /** @var Game|null */
        $game = $session->get("apiGame");

        if (!$game) {
            return new JsonResponse(["Could not fold... no game found"]);
        }

        $folded = $game->getPlayers()[0]->isFolded();
        $actions = $game->getActions();

        $response = new JsonResponse(["Could not fold... round has finished"]);
        if (!$game->isOver() || $folded) {
            $actions->playerFold(0);
            return $this->gamePlay($session, "fold");
        }

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    #[Route("proj/api/call", name: "call", methods: ["POST"])]
    public function playCall(SessionInterface $session): Response
    {
        /** @var Game|null */
        $game = $session->get("apiGame");

        if (!$game) {
            return new JsonResponse(["Could not call... no game found"]);
        }

        $actions = $game->getActions();
        $folded = $game->getPlayers()[0]->isFolded();

        if (!$game->isOver() && !$folded) {
            $actions->playerCall(0);
            return $this->gamePlay($session, "call");
        }

        $response = new JsonResponse(["Could not call... round has finished"]);
        if ($folded) {
            $response = new JsonResponse(["You folded and cant continue"]);
        }

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    #[Route("proj/api/check", name: "check", methods: ["POST"])]
    public function playCheck(SessionInterface $session): Response
    {
        /** @var Game|null */
        $game = $session->get("apiGame");

        if (!$game) {
            return new JsonResponse(["Could not check... no game found"]);
        }

        $folded = $game->getPlayers()[0]->isFolded();
        $actions = $game->getActions();

        if ($game->canCheck(0) && !$game->isOver() && !$folded) {
            $actions->playerCheck(0);
            return $this->gamePlay($session, "check");
        }

        if ($game->isOver()) {
            return new JsonResponse(["Could not check... round has finished"]);
        }

        if ($folded) {
            return new JsonResponse(["You folded and cant continue"]);
        }

        return new JsonResponse(["Could not check...try some other action"]);

    }

    #[Route("proj/api/continue", name: "continue", methods: ["POST"])]
    public function playContinue(SessionInterface $session): Response
    {
        /** @var Game|null */
        $game = $session->get("apiGame");

        if (!$game) {
            return new JsonResponse(["Could not continue... no game found"]);
        }

        $folded = $game->getPlayers()[0]->isFolded();

        if ($game->isOver() || $folded) {
            $game->nextRound();
            return $this->gamePlay($session, "continue");
        }

        $response = new JsonResponse(["Could not continue... make sure round has finished"]);

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
}
