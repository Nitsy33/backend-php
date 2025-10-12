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
      json_out(['error'=>'Forbidden'], 403);
    }
    $members = AreaRepo::membersOf($pdo, $areaId);
    json_out($members);
  }
}
