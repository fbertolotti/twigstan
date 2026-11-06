ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# The static binary bundles its own PHP, because Castor requires a newer PHP than TwigStan
RUN curl -fsSL https://github.com/jolicode/castor/releases/latest/download/castor.linux-amd64 -o /usr/local/bin/castor \
    && chmod +x /usr/local/bin/castor

# PHPStan, which TwigStan runs in subprocesses, needs more than the default 128M
RUN echo 'memory_limit=-1' > /usr/local/etc/php/conf.d/memory.ini

# Created here so the composer-cache volume is writable by any user the container runs as
RUN mkdir -p /tmp/composer /tmp/home && chmod 1777 /tmp/composer /tmp/home

WORKDIR /app
