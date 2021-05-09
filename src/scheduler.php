<?php
$cwd = getcwd();
chdir(__DIR__);

require_once __DIR__ . '/vendor/autoload.php';

use GO\Scheduler;

$scheduler = new Scheduler();

$scheduler->php(__DIR__ . '/jobs/hello_world.php')
    ->everyMinute(5)
    ->output('my_file.log', true);

$scheduler->run();
chdir($cwd);