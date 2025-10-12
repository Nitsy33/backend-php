<?php
declare(strict_types=1);

function b64url_encode(string $data): string {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function b64url_decode(string $data): string {
  $pad = strlen($data) % 4;
  if ($pad) $data .= str_repeat('=', 4 - $pad);
  return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_sign(array $payload, int $expSeconds = 86400): string {
  $header = ['alg' => 'HS256', 'typ' => 'JWT'];
  $payload['iat'] = time();
  $payload['exp'] = time() + $expSeconds;

  $h = b64url_encode(json_encode($header, JSON_UNESCAPED_SLASHES));
  $p = b64url_encode(json_encode($payload, JSON_UNESCAPED_SLASHES));
  $secret = env('JWT_SECRET', 'change_me') ?? 'change_me';
  $sig = b64url_encode(hash_hmac('sha256', "$h.$p", $secret, true));
  return "$h.$p.$sig";
}

function jwt_verify(string $token): ?array {
  $parts = explode('.', $token);
  if (count($parts) !== 3) return null;
  [$h, $p, $s] = $parts;
  $secret = env('JWT_SECRET', 'change_me') ?? 'change_me';
  $calc = b64url_encode(hash_hmac('sha256', "$h.$p", $secret, true));
  if (!hash_equals($calc, $s)) return null;

  $payload = json_decode(b64url_decode($p), true);
  if (!is_array($payload)) return null;
  if (isset($payload['exp']) && time() > (int)$payload['exp']) return null;
  return $payload;
}
