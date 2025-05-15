<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Library;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\LibraryRepository;

class ApiControllerKmom05 extends AbstractController
{
    #[Route("/api/library/books", name: "api_books")]
    public function books(
        LibraryRepository $libraryRepository,
    ): Response {
        $books = $libraryRepository
            ->findAll();
        $response = $this->json($books);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("api/library/book/{isbn}", name: "book_by_isbn")]
    public function bookByIsbn(
        LibraryRepository $libraryRepository,
        int $isbn
    ): Response {

        $books = $libraryRepository
            ->findByIsbn((string) $isbn);
        $response = $this->json($books);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }
}
