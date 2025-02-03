<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    private function getBooksPath(): string
    {
        return $this->getParameter('kernel.project_dir') . '/public/books.json';
    }

    private function loadBooks(): array
    {
        $jsonData = file_get_contents($this->getBooksPath());
        return json_decode($jsonData, true)['books'];
    }

    private function saveBooks(array $books): void
    {
        file_put_contents($this->getBooksPath(), json_encode(['books' => $books], JSON_PRETTY_PRINT));
    }

    #[Route('/books', name: 'book_list')]
    public function index(): Response
    {
        $books = $this->loadBooks();
        return $this->render('books/index.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/books/add', name: 'book_add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $books = $this->loadBooks();

        $newBook = [
            'id' => uniqid(),
            'title' => $request->request->get('title'),
            'author' => $request->request->get('author'),
            'year' => $request->request->get('year'),
            'available' => true,
            'description' => $request->request->get('description'),
        ];

        $books[] = $newBook;
        $this->saveBooks($books);

        return $this->redirectToRoute('book_list');
    }

    #[Route('/books/remove/{id}', name: 'book_remove')]
    public function remove(string $id): Response
    {
        $books = $this->loadBooks();
        $books = array_filter($books, fn($book) => $book['id'] !== $id);

        $this->saveBooks(array_values($books));

        return $this->redirectToRoute('book_list');
    }
}
