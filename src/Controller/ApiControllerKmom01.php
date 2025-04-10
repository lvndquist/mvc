<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiControllerKmom01 extends AbstractController
{
    #[Route("/api", name: "api")]
    public function api(): Response
    {
        return $this->render('report/api.html.twig');
    }

    #[Route("/api/quote", name: "quote")]
    public function quote(): Response
    {
        date_default_timezone_set('Europe/Stockholm');
        $number = random_int(0, 4);

        $data = [
            0 => "Den största äran i livet ligger inte i att aldrig falla, utan i att resa sig varje gång vi faller. - Nelson Mandela",
            1 => "Sättet att komma igång är att sluta prata och börja göra. - Walt Disney",
            2 => "Gråt inte för att det är över, le för att det hände. - Dr. Seuss",
            3 => "Om du vill leva ett lyckligt liv ska du knyta det till ett mål, inte till människor eller saker. - Albert Einstein",
            4 => "Framgång är inte nyckeln till lycka. Lycka är nyckeln till framgång. Om du älskar det du gör kommer du att bli framgångsrik. - Albert Schweitzer"
        ];

        $response = new JsonResponse(
            ["quote" => $data[$number],
            "date" => date("Y-m-d"),
            "generated" => date("Y-m-d H:i:s")
            ]
        );
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        return $response;
    }
}
