<?php
declare(strict_types=1);

function db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;

  $host = env('DB_HOST');
  $port = env('DB_PORT', '5432');
  $db   = env('DB_NAME', 'postgres');
  $user = env('DB_USER');
  $pass = env('DB_PASS');

  if (!$host || !$user || $pass === null) {
    throw new RuntimeException('DB env vars missing (DB_HOST, DB_USER, DB_PASS)');
  }

  $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}
