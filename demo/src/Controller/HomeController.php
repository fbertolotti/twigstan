<?php

declare(strict_types=1);

namespace TwigStan\Demo\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use TwigStan\Demo\PostRepository;

final readonly class HomeController
{
    public function __construct(
        private Environment $twig,
        private PostRepository $posts,
    ) {}

    #[Route('/', name: 'home')]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('home.html.twig', [
            'title' => 'Latest posts',
            'posts' => $this->posts->all(),
        ]));
    }
}
