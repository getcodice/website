<?php

use CodiceWeb\Providers\DocumentationServiceProvider;
use Dotenv\Dotenv;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Response;

chdir('..');

require './vendor/autoload.php';

// Load DotEnv config
$dotenv = new Dotenv(dirname(__DIR__));
$dotenv->load();

$config = require 'config/app.php';
$config2 = require 'config/docs.php';
$config = array_merge($config, $config2);

$app = new Application();

$app['debug'] = true;

$app->register(new DocumentationServiceProvider(), array(
    'docs.config' => $config['docs'],
));

$app->register(new TwigServiceProvider(), array(
    'twig.path' => 'resources/templates/',
));

$app['twig']->addGlobal('base', $config['app']['base_url']);

$app->get('/docs/switch-version/{version}', function (Application $app, $version) use ($config) {
    $response = $app->redirect($config['app']['base_url'] . 'docs');

    $response = $app['docs']->setSelectedVersion($response, $version);

    return $response;
});

$app->get('/docs/{version}/{chapter}', function (Application $app, $version, $chapter) {
    return $app['docs']->displayChapter($app, $chapter, $version);
});

$app->get('/docs/{chapter}', function (Application $app, $chapter) use ($config) {
    // Redirect to an URL containing version
    $version = $app['docs']->getSelectedVersion();

    return $app->redirect($config['app']['base_url'] . "docs/$version/$chapter");
});

// Fixme: enforcing slash is a bit hacky
$app->get('/docs/', function (Application $app) {
    $chapter = 'installation';
    $version = $app['docs']->getSelectedVersion();

    return $app['docs']->displayChapter($app, $chapter, $version);
});

$app->get('/', function (Application $app) use ($config) {
    return 'todo';
});

$app->error(function (Exception $e) use ($app) {
    return new Response(
        $app['twig']->render('errors/' . ($e->getStatusCode() === 404 ? '404' : 'generic') . '.twig'),
        $e->getStatusCode()
    );
});

$app->run();
