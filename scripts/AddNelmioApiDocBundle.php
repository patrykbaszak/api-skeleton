<?php

declare(strict_types=1);

$composer  = json_decode(file_get_contents(__DIR__ . '/../skeleton/composer.json'));

$composer->title ??= 'API';
$composer->description ??= 'Awesome API';
$composer->repositories = [
    (object) [
        'type' => 'vcs',
        'url' => 'https://github.com/DjordyKoert/NelmioApiDocBundle.git'
    ]
];
$composer->require->{'nelmio/api-doc-bundle'} = 'dev-symfony-map-request-data';
$composer->extra->symfony->{'allow-contrib'} = true;

file_put_contents(__DIR__ . '/../skeleton/composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
