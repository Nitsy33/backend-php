<?php
declare(strict_types=1);

function require_auth(): array {
  $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!preg_match('/Bearer\s+(.+)/i', $hdr, $m)) {
    json_out(['error'=>'No token'], 401);
  }
  $payload = jwt_verify($m[1]);
  if (!$payload) json_out(['error'=>'Invalid token'], 401);
  return $payload;
}
