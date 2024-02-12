<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

require_once dirname(__DIR__).'/vendor/autoload.php';

$start = file_get_contents(__DIR__ . '/../skeleton/start.sh');
$composer  = json_decode(file_get_contents(__DIR__ . '/../skeleton/composer.json'));
$routes = Yaml::parse(file_get_contents(__DIR__ . '/../skeleton/config/routes.yaml'));
$bundles = include __DIR__ . '/../skeleton/config/bundles.php';

// setup composer
$composer->title ??= 'API';
$composer->description ??= 'Awesome API';

// setup controller
$routes['controllers']['resource'] = '../src/';

// setup nelmio
$routes['extended_api_doc']['resource'] = '@ExtendedApiDocBundle/Resources/routes/*';
$composer->repositories = [
    (object) [
        'type' => 'vcs',
        'url' => 'https://github.com/DjordyKoert/NelmioApiDocBundle.git'
    ]
];
$composer->require->{'nelmio/api-doc-bundle'} = 'dev-master';
$composer->require->{'pbaszak/extended-api-doc-bundle'} = '^1.1';
$composer->extra->symfony->{'allow-contrib'} = true;
$bundles[Nelmio\ApiDocBundle\NelmioApiDocBundle::class] = ['all' => true];
$bundles[PBaszak\ExtendedApiDoc\ExtendedApiDocBundle::class] = ['all' => true];

// setup twig (required for nelmio)
$composer->require->{'symfony/asset'} = '^6.3';
$composer->require->{'symfony/twig-bundle'} = '^6.3';
$composer->require->{'twig/extra-bundle'} = '^3.0';
$composer->require->{'twig/twig'} = '^3.0';
$bundles[Symfony\Bundle\TwigBundle\TwigBundle::class] = ['all' => true];
$bundles[Twig\Extra\TwigExtraBundle\TwigExtraBundle::class] = ['all' => true];

// setup start script
$search = 'docker run -d --name php \
    --user "$(id -u):$(id -g)" \
    --env-file .env.local \
    -v $(pwd):/app \
    -w /app \
    $IMAGE_NAME bash -c "tail -f /dev/null"';
$replace = 'docker run -d --name php \
    --user "$(id -u):$(id -g)" \
    --env-file .env.local \
    -v $(pwd):/app \
    -w /app \
    -p 8080:8080 \
    $IMAGE_NAME php -S 0.0.0.0:8080 -t public';
$start = str_replace($search, $replace, $start);

file_put_contents(__DIR__ . '/../skeleton/start.sh', $start);
file_put_contents(__DIR__ . '/../skeleton/config/routes.yaml', Yaml::dump($routes, 10, 4));
file_put_contents(__DIR__ . '/../skeleton/composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
file_put_contents(__DIR__ . '/../skeleton/config/bundles.php', '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($bundles, true) . ';' . PHP_EOL);
