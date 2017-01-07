<?php

namespace CodiceWeb;

use Silex\Application as Silex;

class Application extends Silex
{
    public function __construct(array $values = [])
    {
        $values['config'] = $this->loadConfig();

        parent::__construct($values);
    }

    protected function loadConfig()
    {
        return [
            'app' => require 'config/app.php',
            'docs' => require 'config/docs.php',
        ];
    }
}
