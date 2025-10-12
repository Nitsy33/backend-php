<?php
declare(strict_types=1);

function env(string $key, ?string $default = null): ?string {
  $v = getenv($key);
  if ($v !== false) return $v;

  // Leer config/.env una sola vez
  static $vars = null;
  if ($vars === null) {
    $vars = [];
    $path = __DIR__ . '/../../config/.env';
    if (is_file($path)) {
      foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (trim($line) === '' || str_starts_with(trim($line), '#')) continue;
        [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        $vars[$k] = $v;
      }
    }
  }

  return $vars[$key] ?? $default;
}
