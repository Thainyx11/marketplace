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
# Copy the whole repo rather than naming individual files: an earlier
# version only copied resources/ + vite.config.js and missed
# tailwind.config.js + postcss.config.js. Without them Vite's PostCSS step
# never expands the @tailwind at-rules, so the "compiled" CSS shipped to
# the browser was just the literal `@tailwind base;@tailwind components;
# @tailwind utilities;` source — the live site rendered fully unstyled.
COPY . .
# VITE_* vars are compiled into the JS bundle at build time (Vite reads
# import.meta.env then, not at runtime) — unlike Nixpacks, Railway's
# Dockerfile builder doesn't expose service variables to RUN steps unless
# declared as ARGs here. Missing this caused "You must pass your app key
# when you instantiate Pusher" in the browser (Echo initialized with an
# empty key) despite VITE_REVERB_APP_KEY being correctly set in the
# service's variables.
ARG VITE_APP_NAME
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_APP_NAME=$VITE_APP_NAME \
    VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME
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
