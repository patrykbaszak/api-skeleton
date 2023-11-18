<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

require_once dirname(__DIR__).'/vendor/autoload.php';

// setup controller
$routes = Yaml::parse(file_get_contents(__DIR__ . '/../skeleton/config/routes.yaml'));
$routes['controllers']['resource']['path'] = '../src/Presentation/Controller/API/';
$routes['controllers']['resource']['namespace'] = 'App\Presentation\Controller\API';
file_put_contents(__DIR__ . '/../skeleton/config/routes.yaml', Yaml::dump($routes, 10, 4));

// setup start script
$start = file_get_contents(__DIR__ . '/../skeleton/start.sh');
$search = 'docker run -d --name php \
    --env-file .env.local \
    -v $(pwd):/app \
    -w /app \
    $IMAGE_NAME bash -c "tail -f /dev/null"';
$replace = 'docker run -d --name php \
    --env-file .env.local \
    -v $(pwd):/app \
    -w /app \
    -p 8080:8080 \
    $IMAGE_NAME php -S 0.0.0.0:8080 -t public';
$start = str_replace($search, $replace, $start);
file_put_contents(__DIR__ . '/../skeleton/start.sh', $start);
