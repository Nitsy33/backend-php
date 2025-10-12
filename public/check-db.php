<?php
require_once __DIR__ . '/../src/core/env.php';
require_once __DIR__ . '/../src/core/db.php';
header('Content-Type: text/plain');
echo "HOST=" . env('DB_HOST') . PHP_EOL;
echo "USER=" . env('DB_USER') . PHP_EOL;
echo "NAME=" . env('DB_NAME') . PHP_EOL;
$pdo = db();
echo "OK: conectado\n";
