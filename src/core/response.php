<?php
declare(strict_types=1);

function json_out($data, int $code = 200): void {
  header('Content-Type: application/json; charset=utf-8');
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

function read_json(): array {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
