<?php
return [
    'track-open' => true,
    'track-click' => true,
    'expire-days' => 60,
    'emails-per-page' => 30,
    'date-format' => 'm/d/Y g:i a',
    'route' => [
        'prefix' => 'mail-track',
        'middleware' => ['web'],
    ],
    'custom-inject' => false,
    'custom-inject-html' => ''
];