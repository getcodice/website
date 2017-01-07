<?php

namespace CodiceWeb;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class Documentation
{
    const COOKIE_NAME = 'codice_docs_version';
    const COOKIE_TTL = 60 * 60 * 24 * 14;

    protected $app;
    protected $config;
    protected static $versions;

    public function __construct(Application $app, $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function displayChapter(Application $app, $chapter, $version)
    {
        $path = $this->config['path'] . "$version/$chapter.md";

        if (!file_exists($path)) {
            $app->abort(404, 'Page not found');
        }

        $file = file($path, FILE_IGNORE_NEW_LINES);

        // Read ATX-style header of first level
        $title = trim(substr($file[0], 2));

        // Strip first two lines of the file - rest of it is a content itself
        $contents = implode(array_slice($file, 1), "\n");

        $converter = new CommonMarkConverter();
        $contents = $converter->convertToHtml($contents);

        $app['twig']->addGlobal('chapter', $chapter);

        return $app->render('docs/chapter.twig', [
            // Chapter-specific
            'contents' => $contents,
            'title' => $title,
            'version' => $version,

            // Used by parent templates as well
            'chapter' => $chapter,
            'github_url' => $this->config['github_url'],
            'menu' => $this->renderMenu($this->getMenu($version), $chapter),
            'versions' => $this->getVersions(),
        ]);
    }

    public function getVersions() {
        if (empty(self::$versions)) {
            self::$versions = array_values(array_diff(scandir($this->config['path']), ['.', '..']));
        }

        return self::$versions;
    }

    public function getLastVersion() {
        $versions = $this->getVersions();

        // Get rid of "master", keys are numeric and "master" should always be last element
        unset($versions[count($versions) - 1]);

        return array_pop($versions);
    }

    public function getSelectedVersion()
    {
        $urlVersion = $this->app['request_stack']->getCurrentRequest()->attributes->get('version');

        if ($urlVersion) {
            return $urlVersion;
        }

        $version = isset($_COOKIE[self::COOKIE_NAME]) ? $_COOKIE[self::COOKIE_NAME] : null;

        return $this->sanitizeVersion($version);
    }

    public function setSelectedVersion(Response $response, $version)
    {
        $version = $this->sanitizeVersion($version);

        $response->headers->setCookie(new Cookie(self::COOKIE_NAME, $version, time() + self::COOKIE_TTL));

        return $response;
    }

    protected function sanitizeVersion($version)
    {
        return in_array($version, $this->getVersions()) ? $version : $this->getLastVersion();
    }

    public function getMenu($version)
    {
        return require $this->config['path'] . $version . '/_menu.php';
    }

    public function renderMenu($menu, $chapter)
    {
        $output = '';

        foreach ($menu as $element)
        {
            $url = $element[0];
            $text = $element[1];

            if ($url === 'header') {
                $output .= '<li class="docs-menu-header">' . $text . '</li>';
            } elseif (substr($url, 0, 5) === 'http:') {
                $output .= '<li><a href="' . $url . '">' . $text . '</a></li>';
            } else {
                $class = $url === $chapter ? 'active' : '';
                $url = getenv('BASE_URL') . 'docs/' . $this->getSelectedVersion() . '/' . $url;

                $output .= '<li><a href="' . $url . '" class="' . $class . '">' . $text . '</a></li>';
            }
        }

        return $output;
    }
}
