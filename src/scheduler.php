<?php
$cwd = getcwd();
chdir(__DIR__);

require_once __DIR__ . '/vendor/autoload.php';

use GO\Scheduler;

$scheduler = new Scheduler();

$scheduler->php(__DIR__ . '/jobs/download_auth0_jwks.php')
    ->hourly()
    ->output('download_auth0_jwks.log', true);

$scheduler->php(__DIR__ . '/jobs/generate_jwk_set.php')
    ->hourly()
    ->output('generate_jwk_set.log', true);

$scheduler->run();
chdir($cwd);