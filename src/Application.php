<?php

namespace CodiceWeb;

use Silex\Application as Silex;
use Silex\Application\TwigTrait;
use Symfony\Component\HttpFoundation\JsonResponse;

class Application extends Silex
{
    use TwigTrait;

    public function __construct(array $values = [])
    {
        $values['config'] = $this->loadConfig();

        parent::__construct($values);
    }

    public function json($data = [], $status = 200, array $headers = [])
    {
        $response = new JsonResponse($data, $status, $headers);
        $response->setEncodingOptions(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $response;
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
