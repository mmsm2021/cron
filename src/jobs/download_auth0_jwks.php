<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

try {
    $successCodes = [200, 201, 202, 203, 204, 205, 206, 207, 208, 226];
    $container = (new \DI\ContainerBuilder())
        ->addDefinitions(dirname(__DIR__) . '/definitions.php')
        ->build();

    /** @var \GuzzleHttp\ClientInterface $client */
    $client = $container->get(\GuzzleHttp\ClientInterface::class);
    $uri = $container->get('jwks_uri');

    $response = $client->request(
        'GET',
        $uri
    );

    if (!in_array($response->getStatusCode(), $successCodes)) {
        echo 'Non success code returned' . PHP_EOL;
        exit(1);
    }

    $contents = $response->getBody()->getContents();
    $validator = \Respect\Validation\Validator::stringType()->notEmpty()->json();

    if (!$validator->validate($contents)) {
        echo $validator->reportError($contents)->getMessage() . PHP_EOL;
        exit(1);
    }

    if (!file_exists('/keys')) {
        mkdir('/keys', 0777, true);
    }

    $file = '/keys/auth0_jwks.json';

    if (file_put_contents($file, $contents, LOCK_EX) === false) {
        echo 'Failed to write contents to file: "' . $file . '".' . PHP_EOL;
        exit(1);
    }

    if (!chmod($file, 0777)) {
        echo 'Failed to change permissions of output file: "' . $file . '".' . PHP_EOL;
    }

} catch (\Throwable $exception) {
    echo $exception . PHP_EOL;
    exit(1);
}