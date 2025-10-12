<?php
declare(strict_types=1);

function required_string($value, string $field): string {
  $v = trim((string)($value ?? ''));
  if ($v === '') json_out(['error'=>"$field es requerido"], 400);
  return $v;
}

function require_int($value, string $field): int {
  if (!isset($value) || !is_numeric($value)) json_out(['error'=>"$field debe ser número"], 400);
  return (int)$value;
}

function one_of(string $v, array $allowed, string $field): string {
  if (!in_array($v, $allowed, true)) json_out(['error'=>"$field inválido"], 400);
  return $v;
}

function require_enum($value, array $allowed, string $field): string {
  $v = required_string($value, $field);
  return one_of($v, $allowed, $field);
}

function pg_iso_datetime(?string $date): ?string {
  if (!$date) return null;
  $ts = strtotime($date);
  if ($ts === false) json_out(['error'=>'Fecha inválida'], 400);
  return date('c', $ts);
}
