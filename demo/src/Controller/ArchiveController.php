<?php

declare(strict_types=1);

namespace TwigStan\Demo\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use TwigStan\Demo\PostRepository;

final readonly class ArchiveController
{
    public function __construct(
        private Environment $twig,
        private PostRepository $posts,
    ) {}

    #[Route('/archive', name: 'archive')]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('post/archive.html.twig', [
            'title' => 'Archive',
            'posts' => $this->posts->all(),
            'latest' => $this->posts->find(1),
        ]));
    }
}
