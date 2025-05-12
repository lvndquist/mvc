<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Library;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\LibraryRepository;

final class LibraryController extends AbstractController
{
    #[Route('/library', name: 'library')]
    public function index(): Response
    {
        return $this->render('library/index.html.twig');
    }

    #[Route('/library/create', name: 'book_create', methods: ["GET"])]
    public function create(): Response
    {
        return $this->render('library/create.html.twig');
    }

    #[Route('/library/create', name: 'post_book_create', methods: ["POST"])]
    public function createBook(
        Request $request,
        ManagerRegistry $doctrine
    ): Response {
        $title = (string) $request->request->get('title');
        $author = (string) $request->request->get('author');
        $isbn = (string) $request->request->get('isbn');
        $url = (string) $request->request->get('img_url');

        $entityManager = $doctrine->getManager();
        $book = new Library();
        $book->setTitle($title);
        $book->setAuthor($author);
        $book->setIsbn($isbn);
        $book->setImageUrl($url);

        // tell Doctrine you want to (eventually) save the Product
        // (no queries yet)
        $entityManager->persist($book);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        $this->addFlash(
            'notice',
            "Saved new book with id '" . $book->getId() . "'"
        );

        return $this->redirectToRoute('library_show_all');
    }

    #[Route('/library/show/{id}', name: 'book_by_id')]
    public function showBookById(
        LibraryRepository $libraryRepository,
        int $id
    ): Response {
        $book = $libraryRepository
            ->find($id);

        $data = [
            "book" => $book
        ];

        return $this->render('library/show-detail.html.twig', $data);
    }

    #[Route('/library/show', name: 'library_show_all')]
    public function showAllBooks(
        LibraryRepository $libraryRepository
    ): Response {
        $books = $libraryRepository
            ->findAll();

        $data = [
            "books" => $books
        ];

        return $this->render('library/show-all.html.twig', $data);
    }

    #[Route('/library/update/{id}', name: 'update_book_by_id', methods: ["GET"])]
    public function update(
        LibraryRepository $libraryRepository,
        int $id
    ): Response {
        $book = $libraryRepository
            ->find($id);

        $data = [
            "book" => $book
        ];

        return $this->render('library/update.html.twig', $data);
    }

    #[Route('/library/update/{id}', name: 'post_update_book_by_id', methods: ["POST"])]
    public function updateBook(
        ManagerRegistry $doctrine,
        Request $request,
        int $id
    ): Response {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Library::class)->find($id);

        if (!$book) {
            /*
            throw $this->createNotFoundException(
                'No book found for id '.$id
            );
            */
            $this->addFlash(
                'warning',
                "Could not update book with id '" . $id . "'"
            );
            return $this->redirectToRoute('library_show_all');
        }

        $title = (string) $request->request->get('title');
        $author = (string) $request->request->get('author');
        $isbn = (string) $request->request->get('isbn');
        $url = (string) $request->request->get('url');

        $book->setTitle($title);
        $book->setAuthor($author);
        $book->setIsbn($isbn);
        $book->setImageUrl($url);

        $entityManager->flush();

        $this->addFlash(
            'notice',
            "Updated book with id '" . $id . "'"
        );

        return $this->redirectToRoute('library_show_all');
    }

    #[Route('/library/delete/{id}', name: 'delete_book_by_id', methods: ["GET"])]
    public function delete(
        LibraryRepository $libraryRepository,
        int $id
    ): Response {
        $book = $libraryRepository
            ->find($id);

        $data = [
            "book" => $book
        ];

        return $this->render('library/delete.html.twig', $data);
    }

    #[Route('/library/delete/{id}', name: 'post_delete_book_by_id', methods: ["POST"])]
    public function deleteBook(
        ManagerRegistry $doctrine,
        int $id
    ): Response {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Library::class)->find($id);

        if (!$book) {
            /*
            throw $this->createNotFoundException(
                'No book found for id '.$id
            ); */

            $this->addFlash(
                'warning',
                "Could not remove book with id '" . $id . "'"
            );
            return $this->redirectToRoute('library_show_all');
        }

        $entityManager->remove($book);
        $entityManager->flush();

        $this->addFlash(
            'notice',
            "Removed book with id '" . $id . "'"
        );

        return $this->redirectToRoute('library_show_all');
    }
}
