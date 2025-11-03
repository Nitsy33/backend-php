<?php
declare(strict_types=1);

class AreaRepo {
  public static function areasOfUser(PDO $pdo, int $userId): array {
    $st = $pdo->prepare("SELECT a.* FROM area a
                         JOIN area_member am ON am.area_id=a.id
                         WHERE am.user_id=:u
                         ORDER BY a.id ASC");
    $st->execute([':u'=>$userId]);
    return $st->fetchAll();
  }

  public static function membersOf(PDO $pdo, int $areaId): array {
    $st = $pdo->prepare("SELECT ua.id, ua.name, ua.email, am.role
                         FROM area_member am
                         JOIN user_account ua ON ua.id = am.user_id
                         WHERE am.area_id = :a
                         ORDER BY ua.name ASC");
    $st->execute([':a'=>$areaId]);
    return $st->fetchAll();
  }
    // ✅ NUEVO: un miembro específico por ID dentro de un área
  public static function memberById(PDO $pdo, int $areaId, int $userId): ?array {
    $st = $pdo->prepare("SELECT ua.id, ua.name, ua.email, am.role
                         FROM area_member am
                         JOIN user_account ua ON ua.id = am.user_id
                         WHERE am.area_id = :a
                           AND am.user_id = :u
                         LIMIT 1");
    $st->execute([':a' => $areaId, ':u' => $userId]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
  }

  public static function isCoordinator(PDO $pdo, int $areaId, int $userId): bool {
    $st = $pdo->prepare("SELECT 1 FROM area WHERE id=:a AND coordinator_id=:u");
    $st->execute([':a'=>$areaId, ':u'=>$userId]);
    return (bool)$st->fetchColumn();
  }
}
