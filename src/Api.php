<?php

namespace CodiceWeb;

class Api
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getReleases()
    {
        // Get GitHub API response
        $releases = $this->getCachedResponse('codice_releases', function () {
            return json_decode($this->getGithubResponse('repos/getcodice/codice/releases'), true);
        });

        // Filter out drafts
        $releases = array_filter($releases, function ($release) {
            return $release['draft'] === false;
        });

        // Leave only desired values and use desired keys
        $response = [];

        foreach ($releases as $release) {
            $version = $release['tag_name'];

            $response[$version] = [
                'version' => $version,
                'release_date' => $release['created_at'],
                'changelog' => $release['body'],
                'changelog_url' => "https://github.com/getcodice/codice/releases/{$version}",
                'download_url' => "https://github.com/getcodice/codice/releases/download/{$version}/{$version}-prepackaged.zip",
            ];
        }

        return $response;
    }

    public function getRelease($version)
    {
        $releases = $this->getReleases();

        if ($version === 'latest') {
            return array_values($releases)[0];
        } elseif (isset($releases[$version])) {
            return $releases[$version];
        } else {
            return [];
        }
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
