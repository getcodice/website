<?php

use CodiceWeb\Providers\DocumentationServiceProvider;
use Dotenv\Dotenv;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

$app->get('/docs', function (Application $app) {
    $chapter = 'installation';
    $version = $app['docs']->getSelectedVersion();

    return $app['docs']->displayChapter($app, $chapter, $version);
});

$app->get('/', function (Application $app) use ($config) {
    return 'todo';
});

$app->error(function (HttpException $e) use($app, $config) {
    if ($e->getStatusCode() == 404) {
        return new Response(
            $app['twig']->render(
                'docs/404.twig',
                [
                    'chapter' => '',
                    'github_url' => $config['docs']['github_url'],
                    'menu' => $app['docs']->renderMenu($app['docs']->getMenu($app['docs']->getSelectedVersion()), ''),
                    'version' => $app['docs']->getSelectedVersion(),
                    'versions' => $app['docs']->getVersions(),
                ]
            ), 
            $e->getStatusCode()
        );
    }
});

$app->run();
