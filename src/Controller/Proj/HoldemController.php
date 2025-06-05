<?php

namespace App\Controller\Proj;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Proj\Game;
use App\Proj\PlayerActions;
use App\Proj\Computer;

class HoldemController extends AbstractController
{
    #[Route("/proj", name: "proj")]
    public function index(): Response
    {
        return $this->render('proj/index.html.twig');
    }

    #[Route("/proj/about", name: "proj_about")]
    public function about(): Response
    {
        return $this->render('proj/about.html.twig');
    }

    #[Route("/proj/start", name: "start_game", methods: ["POST"])]
    public function start(SessionInterface $session, Request $request): Response
    {
        $name = (string) $request->request->get('name');
        $startingMoney = (int) $request->request->get('money');
        $setOpenCards = $request->request->has('open_cards');
        $setHelp = $request->request->has('help_log');
        $setFullHelp = $request->request->has('full_log');

        $game = new Game($startingMoney, $name, $setHelp, $setFullHelp, $setOpenCards);
        $actions = new PlayerActions($game);
        $computer = new Computer($game, $actions);

        $game->start($computer, $actions);

        $session->set("settings", [
            "money" => $startingMoney,
            "name" => $name,
            "help" => $setHelp,
            "fullHelp" => $setFullHelp,
            "openCards" => $setOpenCards,
            "consoleDebug" => true
        ]);
        $session->set("game", $game);

        return $this->redirectToRoute('holdem');
    }

    #[Route("/proj/game", name: "holdem")]
    public function holdem(SessionInterface $session): Response
    {
        /** @var Game $game */
        $game = $session->get("game");
        $settings = $session->get("settings");

        if (!is_array($settings) ||
            !isset($settings["money"], $settings["name"], $settings["help"],
                $settings["fullHelp"], $settings["openCards"], $settings["consoleDebug"]
            )
        ) {
            $settings = [
                "money" => 5000,
                "name" => "player",
                "help" => false,
                "fullHelp" => false,
                "openCards" => false,
                "consoleDebug" => true
            ];
        }

        $game->updateGameState();
        $players = $game->getPlayers();
        foreach ($players as $player) {
            $game->setEvaluation($player);
        }

        $isOver = $game->isOver();
        $computerTurn = $players[$game->getCurrPlayerIndex()]->isComputer();
        $dealerCards = $game->getDealerCards();
        $currIndex = $game->getCurrPlayerIndex();
        $useHelp = $game->getUseHelp();

        $data = [
            "players" => $players,
            "winner" => $game->getWinner(),
            "currPlayerIndex" => $currIndex,
            "pot" => $game->getPot(),
            "canCheck" => $game->canCheck($currIndex),
            "dealerCards" => $dealerCards,
            "log" => $game->getLog(),
            "computerTurn" => $computerTurn,
            "phase" => $game->getPhase(),
            "openCards" => $game->getUseOpenCards(),
            "useFullHelp" => $game->getUseFullHelp(),
            "useHelp" => $useHelp,
            "help" => $currIndex === 0 && $useHelp ? $players[0]->getEvaluatedString() : "",
            "isOver" => $isOver,
            "consoleDebug" => $settings["consoleDebug"]
        ];

        //$session->set("game", $game);
        return $this->render('proj/game.html.twig', $data);
    }

    #[Route('/proj/user-input', name: 'user_input', methods: ['POST'])]
    public function userInput(Request $request, SessionInterface $session): Response
    {
        /** @var Game $game */
        $game = $session->get("game");
        $actions = $game->getActions();

        if ($request->request->has("fold")) {
            $actions->playerFold(0);
        } elseif ($request->request->has("call")) {
            $actions->playerCall(0);
        } elseif ($request->request->has("raise")) {
            $amount = (int) $request->request->get('amount');
            $actions->playerRaise(0, $amount);
        } elseif ($request->request->has("check")) {
            if ($game->canCheck(0)) {
                $actions->playerCheck(0);
            }
        } elseif ($request->request->has("continue")) {
            $game->nextRound();
        } elseif ($request->request->has("reset")) {
            $settings = $session->get("settings");
            if (!is_array($settings) ||
                !isset($settings["money"], $settings["name"], $settings["help"],
                    $settings["fullHelp"], $settings["openCards"], $settings["consoleDebug"]
                )
            ) {
                $settings = [
                    "money" => 5000,
                    "name" => "player",
                    "help" => false,
                    "fullHelp" => false,
                    "openCards" => false,
                    "consoleDebug" => true
                ];
            }
            $game = new Game($settings["money"], $settings["name"], $settings["help"], $settings["fullHelp"], $settings["openCards"]);
        }
        $session->set("game", $game);
        return $this->redirectToRoute('holdem');
    }
}
