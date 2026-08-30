# Deployment-only image (Railway). Not used for local development, which
# runs directly on the host per README (php artisan serve + vite + reverb).
#
# Built as one shared image for both Railway services (web, reverb) — the
# actual process each runs is set per-service via Railway's Start Command,
# not this file's CMD (only a local fallback).

# ---- Stage 1: build frontend assets ----
FROM node:20-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY resources resources
COPY vite.config.js ./
RUN npm run build

# ---- Stage 2: PHP runtime ----
FROM php:8.4-cli

# pcntl/posix: required by Reverb's server process for signal handling
# (SIGINT/SIGTERM) — confirmed missing from Railway's default Nixpacks PHP
# build, which crashed reverb:start with "Undefined constant ... SIGINT".
# Not required locally (Windows PHP has neither extension at all), so this
# stays a Dockerfile-only dependency instead of a composer.json requirement
# — adding it there broke `composer install` on Windows dev and in CI.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring pcntl posix bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 8080
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
