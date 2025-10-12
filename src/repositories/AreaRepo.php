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

  public static function isCoordinator(PDO $pdo, int $areaId, int $userId): bool {
    $st = $pdo->prepare("SELECT 1 FROM area WHERE id=:a AND coordinator_id=:u");
    $st->execute([':a'=>$areaId, ':u'=>$userId]);
    return (bool)$st->fetchColumn();
  }
}
