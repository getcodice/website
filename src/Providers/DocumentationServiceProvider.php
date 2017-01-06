<?php

namespace CodiceWeb\Providers;

use CodiceWeb\Documentation;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;

class DocumentationServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['docs'] = function ($app) {
            return new Documentation($app, $app['docs.config']);
        };
    }
}
