<?php

declare(strict_types=1);

namespace TwigStan\Demo\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use TwigStan\Demo\PostRepository;

final readonly class PostController
{
    public function __construct(
        private Environment $twig,
        private PostRepository $posts,
    ) {}

    #[Route('/post/{id}', name: 'post_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $post = $this->posts->find($id);

        if ($post === null) {
            return new Response('Not found', Response::HTTP_NOT_FOUND);
        }

        return new Response($this->twig->render('post/show.html.twig', [
            'title' => $post->title,
            'post' => $post,
        ]));
    }
}
