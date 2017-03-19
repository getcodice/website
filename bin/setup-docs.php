#!/usr/bin/env php
<?php

$gitRepository = 'https://github.com/getcodice/docs.git';
$args = array_slice($argv, 1);
$branches = explode(',', get_option('-b', '0.4,0.5,master'));
$rootDir = rtrim(get_option('-d', __DIR__.'/../docs'), '/');
$lastArgumentIndex = count($args) - 1;

if (in_array('--help', $args) || in_array('-h', $args)) {
    echo <<<HELP
Downloads and updates Codice documentation.

Usage:
  {$argv[0]} [-b BRANCHES] [-d DESTDIR]

Options:
  -b  Comma-delimited set of branches to be cloned.
  -d  The root directory to act on, default: docs/

HELP;
    exit(0);
}

if (!is_dir($rootDir)) {
    if (file_exists($rootDir)) {
        fprintf(STDERR, "{$rootDir} exists but is not a directory. Cannot continue.\n");
        exit(1);
    }
    mkdir($rootDir);
}
chdir($rootDir);

function get_option($option, $defaultValue = null)
{
    global $args, $lastArgumentIndex;

    if (($optionIndex = array_search($option, $args)) !== false) {
        if ($optionIndex === $lastArgumentIndex) {
            fprintf(STDERR, "${option}: argument required\n");
            exit(1);
        }

        return $args[$optionIndex + 1];
    }

    return $defaultValue;
}

function execute($command)
{
    echo "> {$command}\n";
    system($command, $exitCode);

    if ($exitCode) {
        fprintf(STDERR, "! {$command} exitted with {$exitCode}");
        exit(1);
    }
}

foreach ($branches as $branch) {
    if (is_dir("{$rootDir}/{$branch}")) {
        if (is_dir("{$rootDir}/{$branch}/.git")) {
            $oldWorkingDir = getcwd();
            echo "entering {$rootDir}/{$branch}\n";
            chdir("{$rootDir}/{$branch}");
            execute("git pull");
            chdir($oldWorkingDir);
        }
    } else {
        execute("git clone ${gitRepository} -b {$branch} {$rootDir}/{$branch}");
    }
}
