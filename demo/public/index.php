<?php

declare(strict_types=1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$twig = require dirname(__DIR__) . '/twig-loader.php';
$posts = new TwigStan\Demo\PostRepository();

$classLoader = new class extends AttributeClassLoader {
    protected function configureRoute(Route $route, ReflectionClass $class, ReflectionMethod $method, object $annot): void
    {
        $route->setDefault('_controller', $class->getName() . '::' . $method->getName());
    }
};
$routes = (new AttributeDirectoryLoader(new FileLocator(dirname(__DIR__) . '/src/Controller'), $classLoader))->load('.');

$resolver = new class($twig, $posts) extends ControllerResolver {
    public function __construct(private object $twig, private object $posts)
    {
        parent::__construct();
    }

    protected function instantiateController(string $class): object
    {
        return new $class($this->twig, $this->posts);
    }
};

$request = Request::createFromGlobals();
$dispatcher = new EventDispatcher();
$requestStack = new RequestStack();
$dispatcher->addSubscriber(new RouterListener(new UrlMatcher($routes, (new RequestContext())->fromRequest($request)), $requestStack));

$kernel = new HttpKernel($dispatcher, $resolver, $requestStack, new ArgumentResolver());
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
