#!/usr/bin/php
<?php

// The unix socket via which the client and server will communicate.
use GabeSullice\PhpFanout\FcgiJob;
use GabeSullice\PhpFanout\PhpFpm;

require_once './vendor/autoload.php';

$socket_file = tempnam(sys_get_temp_dir(), 'phpfanout');

/**
 * Start PHP-FPM to manage multiple processes.
 */
$server = new PhpFpm($socket_file);
$server->start();

/**
 * Create a job that will be executed by the PHP-FPM processes and launch it.
 */
$job = new FcgiJob($socket_file);
$job->execute();

/**
 * Shut the php-fpm process down.
 */
$server->stop();
