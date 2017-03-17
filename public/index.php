<?php

use CodiceWeb\Application;
use CodiceWeb\Providers\DocumentationServiceProvider;
use Dotenv\Dotenv;
use Moust\Silex\Provider\CacheServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

chdir('..');

require './vendor/autoload.php';

// Load DotEnv config
$dotenv = new Dotenv(dirname(__DIR__));
$dotenv->load();

$app = new Application();

$app['debug'] = $app['config']['app']['debug'];

$app->register(new CacheServiceProvider(), [
    'cache.options' => [
        'driver' => 'file',
        'cache_dir' => './data/cache'
    ]
]);

$app->register(new DocumentationServiceProvider(), array(
    'docs.config' => $app['config']['docs'],
));

$app->register(new TwigServiceProvider(), [
    'twig.path' => 'resources/templates/',
    'twig.options' => [
        'cache' => './data/cache/twig'
    ],
]);

$app['twig']->addGlobal('base', $app['config']['app']['base_url']);
$app['twig']->addGlobal('analytics', $app['config']['app']['analytics']);

$app->get('/docs/switch-version/{version}', function (Application $app, $version) {
    $response = $app->redirect('docs');

    $response = $app['docs']->setSelectedVersion($response, $version);

    return $response;
});

$app->get('/docs/{version}/images/{name}.png', function (Application $app, $version, $name) {
    $path = $app['config']['docs']['path'] . $version . '/images/' . $name . '.png';

    if (!preg_match('#^[a-zA-Z0-9-]+$#', $name) || !file_exists($path)) {
        $app->abort(404, 'Page not found');
    }

    $response = new BinaryFileResponse($path, 200, ['content-type' => 'image/png']);

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

$app->get('/docs', function (Application $app) {
    $version = $app['docs']->getSelectedVersion();
    $chapter = $app['docs']->getDefaultChapter($version);

    return $app['docs']->displayChapter($app, $chapter, $version);
});

$app->get('/', function (Application $app) {
    $currentVersion = 'v0.5.0';
    $repository = $app['config']['app']['github_url'];

    return $app->render('home.twig', [
        'changelog_url' => "{$repository}/releases/{$currentVersion}", 
        'expiration_date' => date('m/d/Y', strtotime('+1 day')),
        'download_url' => "{$repository}/releases/download/{$currentVersion}/{$currentVersion}-prepackaged.zip",
        'download_version' => $currentVersion,
        'github_url' => $app['config']['app']['github_url'],
        'home' => true,
    ]);
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

    return new Response($app->renderView("errors/{$template}.twig"), $statusCode);
});

$app->run();
