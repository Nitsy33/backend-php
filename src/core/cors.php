<?php
declare(strict_types=1);

$origins = array_map('trim', explode(',', env('CORS_ORIGIN', '*') ?? '*'));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
$allow = in_array('*', $origins, true) || in_array($origin, $origins, true) ? $origin : ($origins[0] ?? '*');

header("Access-Control-Allow-Origin: $allow");
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
  http_response_code(204);
  exit;
}
