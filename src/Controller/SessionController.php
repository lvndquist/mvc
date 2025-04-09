<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionController extends AbstractController
{

    #[Route("/session", name: "session", methods: ['GET'])]
    public function session(
        SessionInterface $session
    ): Response
    {

        $sessionData = $session -> all();
        $data = [
            "session" => $sessionData
        ];
        return $this->render('card/session.html.twig', $data);
    }

    #[Route("/session/delete", name: "session_delete", methods: ['GET'])]
    public function deleteSession(
        SessionInterface $session
    ): Response
    {
        $session->clear();

        $this->addFlash(
            'notice',
            'Session was deleted!'
        );

        return $this->redirectToRoute('session');
    }
}
