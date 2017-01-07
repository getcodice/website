<?php
return [
    'base_url' => getenv('BASE_URL'),
    'debug' => getenv('APP_DEBUG') === 'true', // fixme: find the way to cast properly
];
