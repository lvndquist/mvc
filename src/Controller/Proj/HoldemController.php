<?php

namespace App\Controller\Proj;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Proj\Game;

class HoldemController extends AbstractController
{
    #[Route("/proj", name: "proj")]
    public function index(): Response
    {
        return $this->render('proj/index.html.twig');
    }

    #[Route("/proj/about", name: "about")]
    public function about(): Response
    {
        return $this->render('proj/index.html.twig');
    }

    #[Route("/proj/start", name: "start_game", methods: ["POST"])]
    public function start(SessionInterface $session, Request $request): Response
    {
        $name = (string) $request->request->get('name');
        $startingMoney = (int) $request->request->get('money');
        $setOpenCards = $request->request->has('open_cards');
        $setHelpLog = $request->request->has('help_log');
        $setFullLog = $request->request->has('full_log');

        $game = new Game($startingMoney, $name, $setHelpLog, $setFullLog, $setOpenCards);

        $session->set("game", $game);

        return $this->redirectToRoute('holdem');
    }

    #[Route("/proj/game", name: "holdem")]
    public function holdem(SessionInterface $session): Response
    {
        /** @var Game|null $game */
        $game = $session->get("game");
        $game->updateGameState();
        $players = $game->getPlayers();
        foreach ($players as $player) {
            $game->setEvaluation($player);
        }
        $computerTurn = $players[$game->getCurrPlayerIndex()]->isComputer();
        $dealerCards = $game->getDealerCards();
        $currIndex = $game->getCurrPlayerIndex();
        $useHelp = $game->getUseHelp();
        $data = [
            "players" => $players,
            "currPlayerIndex" => $currIndex,
            "pot" => $game->getPot(),
            "canCheck" => $game->canCheck($currIndex),
            "dealerCards" => $dealerCards,
            "log" => $game->getLog(),
            "computerTurn" => $computerTurn,
            "phase" => $game->getPhase(),
            "openCards" => $game->getUseOpenCards(),
            "useFullLog" => $game->getUseFullLog(),
            "useHelp" => $useHelp,
            "help" => $currIndex === 0 && $useHelp ? $players[0]->getEvaluatedString() : ""
        ];
        return $this->render('proj/game.html.twig', $data);
    }

    #[Route('/proj/user-input', name: 'user_input', methods: ['POST'])]
    public function userInput(Request $request, SessionInterface $session): Response
    {
        /** @var Game|null $game */
        $game = $session->get("game");

        if ($request->request->has("fold")) {
            $game->playerFold(0);
        } elseif ($request->request->has("call")) {
            $game->playerCall(0);
        } elseif ($request->request->has("raise")) {
            $amount = $request->request->getInt("money");
            $game->playerRaise(0, $amount);
        } elseif ($request->request->has("check")) {
            if ($game->canCheck(0)) {
                $game->playerCheck(0);
            }
        }
        $session->set("game", $game);
        return $this->redirectToRoute('holdem');
    }
}
