#!/bin/php
<?php

use CodiceWeb\Application;
use CodiceWeb\Providers\DocumentationServiceProvider;

chdir('..');

require 'vendor/autoload.php';

$app = new Application();

$app->register(new DocumentationServiceProvider(), array(
    'docs.config' => $app['config']['docs'],
));

// Remove images dir and recreate it from scratch
rmdir_recursive('public/docs/images/');
mkdir('public/docs/images/', 0700, true);

foreach ($app['docs']->getVersions() as $version)
{
    // First make a directory for this version
    mkdir("public/docs/images/{$version}");

    $images = glob("{$app['config']['docs']['path']}{$version}/images/*");
    
    foreach ($images as $source) {
        $filename = array_reverse(explode('/', $source))[0];

        copy($source, "public/docs/images/{$version}/$filename");
    }
}

// Authored by Perer Cowburn (@salathe), found on SO
function rmdir_recursive($dir)
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo)
    {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }

    rmdir($dir);
}
