<?php
declare(strict_types=1);

class UserRepo {
  public static function findByEmail(PDO $pdo, string $email): ?array {
    $st = $pdo->prepare("SELECT id, name, email, is_active FROM user_account WHERE email=:e AND is_active=TRUE");
    $st->execute([':e'=>$email]);
    $u = $st->fetch();
    return $u ?: null;
  }

  public static function userAreas(PDO $pdo, int $userId): array {
    $st = $pdo->prepare("SELECT area_id FROM area_member WHERE user_id=:u");
    $st->execute([':u'=>$userId]);
    return array_column($st->fetchAll(), 'area_id');
  }

  public static function me(PDO $pdo, int $userId): ?array {
    $st = $pdo->prepare("SELECT id, name, email, is_active FROM user_account WHERE id=:id");
    $st->execute([':id'=>$userId]);
    $u = $st->fetch();
    if (!$u) return null;
    $u['areas'] = self::userAreas($pdo, (int)$u['id']);
    return $u;
  }
}
