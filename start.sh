#!/bin/bash

# composer create-project pbaszak/skeleton --no-interaction

rm -rf skeleton/src
cp -r src/src skeleton/src
cp src/Dockerfile skeleton/Dockerfile

docker run --rm -v $(pwd)/skeleton:/app -w /app composer:latest composer require twig asset --no-interaction
docker run --rm -v $(pwd):/app -w /app php:latest php scripts/Setup.php

# ApiDoc Bundle
cp src/config/routes/nelmio_api_doc.yaml skeleton/config/routes/nelmio_api_doc.yaml
cp src/config/packages/nelmio_api_doc.yaml skeleton/config/packages/nelmio_api_doc.yaml
docker run --rm -v $(pwd):/app -w /app php:latest php scripts/AddNelmioApiDocBundle.php

rm -rf node_modules scripts src .gitignore CHANGELOG.md composer.json composer.lock README.md LICENSE .git vendor start.sh package.json package-lock.json

mv skeleton/{,.[^.]}* ./

rm -rf skeleton

bash start.sh
docker exec php composer update --no-interaction || composer update --no-interaction
