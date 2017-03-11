<?php
return [
    'analytics' => getenv('ANALYTICS'),
    'base_url' => getenv('BASE_URL'),
    'debug' => getenv('APP_DEBUG') === 'true', // fixme: find the way to cast properly
    'github_url' => 'https://github.com/getcodice/codice',
];
