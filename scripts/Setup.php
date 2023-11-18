<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

require_once dirname(__DIR__).'/vendor/autoload.php';

$start = file_get_contents(__DIR__ . '/../skeleton/start.sh');
$composer  = json_decode(file_get_contents(__DIR__ . '/../skeleton/composer.json'));
$routes = Yaml::parse(file_get_contents(__DIR__ . '/../skeleton/config/routes.yaml'));

// setup composer
$composer->title ??= 'API';
$composer->description ??= 'Awesome API';

// setup controller
$routes['controllers']['resource']['path'] = '../src/Presentation/Controller/API/';
$routes['controllers']['resource']['namespace'] = 'App\Presentation\Controller\API';

// setup nelmio
$routes['extended_api_doc']['resource'] = '@ExtendedApiDocBundle/Resources/routes/*';
$composer->repositories = [
    (object) [
        'type' => 'vcs',
        'url' => 'https://github.com/DjordyKoert/NelmioApiDocBundle.git'
    ]
];
$composer->require->{'nelmio/api-doc-bundle'} = 'dev-symfony-map-request-data';
$composer->extra->symfony->{'allow-contrib'} = true;

// setup start script
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
file_put_contents(__DIR__ . '/../skeleton/config/routes.yaml', Yaml::dump($routes, 10, 4));
file_put_contents(__DIR__ . '/../skeleton/composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
