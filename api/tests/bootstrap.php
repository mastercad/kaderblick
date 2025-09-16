<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env')) {
    // Lade zuerst die Basis .env
    (new Dotenv())->load(dirname(__DIR__) . '/.env');
}

if (file_exists(dirname(__DIR__) . '/.env.test')) {
    // Lade dann die .env.test, die die Basis-Werte überschreibt
    (new Dotenv())->load(dirname(__DIR__) . '/.env.test');
}
