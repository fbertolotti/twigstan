<?php

declare(strict_types=1);

namespace TwigStan\Demo\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use TwigStan\Demo\PostRepository;

final readonly class ErrorsController
{
    public function __construct(
        private Environment $twig,
        private PostRepository $posts,
    ) {}

    #[Route('/errors', name: 'errors')]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('errors.html.twig', [
            'title' => 'Errors',
            'name' => 'TwigStan',
            'count' => 3,
            'tags' => ['twig', 'phpstan'],
            'latest' => $this->posts->find(1),
            'posts' => $this->posts->all(),
        ]));
    }
}
