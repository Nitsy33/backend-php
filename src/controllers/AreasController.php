<?php
declare(strict_types=1);

class AreasController {
  public static function mine(): void {
    $auth = require_auth();
    $pdo = db();
    $areas = AreaRepo::areasOfUser($pdo, (int)$auth['sub']);
    json_out($areas);
  }

  public static function members(int $areaId): void {
    $auth = require_auth();
    $pdo = db();

    // Debe pertenecer al área
    if (!in_array($areaId, array_map('intval', $auth['areas'] ?? []), true)) {
      json_out(['error' => 'Forbidden'], 403);
      return;
    }

    $members = AreaRepo::membersOf($pdo, $areaId);
    json_out($members);
  }

  // ✅ NUEVO: un usuario específico de un área
  public static function member(int $areaId, int $userId): void {
    $auth = require_auth();
    $pdo = db();

    // Opcional: validar que el usuario autenticado tenga acceso a esa área
    if (!in_array($areaId, array_map('intval', $auth['areas'] ?? []), true)) {
      json_out(['error' => 'Forbidden'], 403);
      return;
    }

    $member = AreaRepo::memberById($pdo, $areaId, $userId);

    if ($member === null) {
      json_out(['error' => 'Usuario no encontrado en el área'], 404);
      return;
    }

    json_out($member, 200);
  }
}

