<?php
$cwd = getcwd();
chdir(__DIR__);

require_once __DIR__ . '/vendor/autoload.php';

use GO\Scheduler;

$scheduler = new Scheduler();

$scheduler->php(__DIR__ . '/jobs/download_auth0_jwks.php')
    ->hourly();

$scheduler->run();
chdir($cwd);