<?php

namespace CodiceWeb\Providers;

use CodiceWeb\Api;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;

class ApiServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['api'] = function ($app) {
            return new Api($app);
        };
    }
}
