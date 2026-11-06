<?php

declare(strict_types=1);

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return new Environment(new FilesystemLoader(__DIR__ . '/templates'));
