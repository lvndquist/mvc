<?php

namespace App\Controller\Game;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Game\GameState;

class GameApiController extends AbstractController
{
    #[Route("/api/game", name: "get_game")]
    public function game(SessionInterface $session): Response
    {
        $gameState = $session->get("gameState");
        $response = new JsonResponse([]);
        if ($gameState instanceof GameState) {
            $player = $gameState->getPlayer();
            $bank = $gameState->getBank();
            $gameIsOver = $gameState->gameIsOver();
            $winner = $gameState->getWinner();
            $numberOfDraws = $gameState->getDrawCounter();
            $response = new JsonResponse(
                [
                    "playerScore" => $player->getScore(),
                    "bankScore" => $bank->getScore(),
                    "numberOfDraws" => $numberOfDraws,
                    "isOver" => $gameIsOver,
                    "winner" => $winner !== -1 ? ($winner == 0 ? "Bank" : "Player") : null
                ]
            );
        }

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        return $response;
    }
}
