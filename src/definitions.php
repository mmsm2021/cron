<?php

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use function DI\env;

return [
    'jwks_uri' => env('JWKS_URI'),
    ClientInterface::class => function() {
        return new Client([
            'verify' => true,
        ]);
    },
];