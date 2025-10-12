<?php
declare(strict_types=1);

function path_segments(): array {
  $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
  $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
  $path = substr($uri, strlen($base));
  return array_values(array_filter(explode('/', $path)));
}

function method(): string {
  return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}
