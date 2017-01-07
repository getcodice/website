<?php

use CodiceWeb\Application;
use CodiceWeb\Providers\DocumentationServiceProvider;
use Dotenv\Dotenv;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

chdir('..');

require './vendor/autoload.php';

// Load DotEnv config
$dotenv = new Dotenv(dirname(__DIR__));
$dotenv->load();

$app = new Application();

$app['debug'] = $app['config']['app']['debug'];

$app->register(new DocumentationServiceProvider(), array(
    'docs.config' => $app['config']['docs'],
));

$app->register(new TwigServiceProvider(), array(
    'twig.path' => 'resources/templates/',
));

$app['twig']->addGlobal('base', $app['config']['app']['base_url']);

$app->get('/docs/switch-version/{version}', function (Application $app, $version) {
    $response = $app->redirect('docs/');

    $response = $app['docs']->setSelectedVersion($response, $version);

    return $response;
});

$app->get('/docs/{version}/{chapter}', function (Application $app, $version, $chapter) {
    return $app['docs']->displayChapter($app, $chapter, $version);
});

$app->get('/docs/{chapter}', function (Application $app, $chapter) {
    // Redirect to an URL containing version
    $version = $app['docs']->getSelectedVersion();

    return $app->redirect("docs/$version/$chapter");
});

// Fixme: enforcing slash is a bit hacky
$app->get('/docs/', function (Application $app) {
    $chapter = 'installation';
    $version = $app['docs']->getSelectedVersion();

    return $app['docs']->displayChapter($app, $chapter, $version);
});

$app->get('/', function (Application $app) {
    return 'todo';
});

$app->error(function (Exception $e) use ($app) {
    if ($app['debug']) {
        throw $e;
    }

    if ($e instanceof HttpException) {
        $statusCode = $e->getStatusCode();
        $template = $e->getStatusCode() === 404 ? '404' : 'generic';
    } else {
        $statusCode = 500;
        $template = 'generic';
    }

    return new Response($app['twig']->render("errors/{$template}.twig"), $statusCode);
});

$app->run();
