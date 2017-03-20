<?php

namespace CodiceWeb;

class Api
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getChangelog($version = null)
    {
        // Get GitHub API response
        $releases = $this->getCachedResponse('codice_changelog', function () {
            return json_decode($this->getGithubResponse('repos/getcodice/codice/releases'), true);
        });

        // Filter out drafts
        $releases = array_filter($releases, function ($release) {
            return $release['draft'] === false;
        });

        // Rewrite response to version => changelog
        $response = [];

        foreach ($releases as $release) {
            $response[$release['tag_name']] = $release['body'];
        }

        // Limit response to single version if requested
        if ($version) {
            $response = $response[$version];
        }

        return $response;
    }

    protected function getCachedResponse($cacheKey, $closure, $ttl = 3600)
    {
        $response = $this->app['cache']->fetch($cacheKey);

        if ($response == null) {
            $response = $closure();

            $this->app['cache']->store($cacheKey, $response, $ttl);
        }

        return $response;
    }

    protected function getGithubResponse($url)
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: codice.eu (github.com/getcodice)'
                ]
            ]
        ];

        $context = stream_context_create($options);

        return file_get_contents('https://api.github.com/' . $url, null, $context);
    }
}
