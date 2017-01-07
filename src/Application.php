<?php

namespace CodiceWeb;

use Silex\Application as Silex;
use Silex\Application\TwigTrait;

class Application extends Silex
{
    use TwigTrait;

    public function __construct(array $values = [])
    {
        $values['config'] = $this->loadConfig();

        parent::__construct($values);
    }

    public function externalRedirect($url, $status = 302)
    {
        return parent::redirect($url, $status);
    }

    public function redirect($url, $status = 302)
    {
        return parent::redirect($this['config']['app']['base_url'] . $url, $status);
    }

    protected function loadConfig()
    {
        return [
            'app' => require 'config/app.php',
            'docs' => require 'config/docs.php',
        ];
    }
}
