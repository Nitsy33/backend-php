<?php
declare(strict_types=1);

class UserRepo {

  // 🔹 Buscar usuario por email
  public static function findByEmail(PDO $pdo, string $email): ?array {
    $st = $pdo->prepare("
      SELECT id, name, email, is_active
      FROM user_account
      WHERE email = :e AND is_active = TRUE
    ");
    $st->execute([':e' => $email]);
    $u = $st->fetch();
    return $u ?: null;
  }

  // 🔹 Obtener las áreas del usuario con su rol
  public static function userAreas(PDO $pdo, int $userId): array {
    $sql = "
      SELECT 
        am.area_id,
        am.role,
        a.name AS area_name
      FROM area_member am
      JOIN area a ON a.id = am.area_id
      WHERE am.user_id = :u
    ";
    $st = $pdo->prepare($sql);
    $st->execute([':u' => $userId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  // 🔹 Obtener información completa del usuario autenticado
  public static function me(PDO $pdo, int $userId): ?array {
    $st = $pdo->prepare("
      SELECT id, name, email, is_active
      FROM user_account
      WHERE id = :id
    ");
    $st->execute([':id' => $userId]);
    $u = $st->fetch();
    if (!$u) return null;

    // Agregar sus áreas y roles
    $u['areas'] = self::userAreas($pdo, (int)$u['id']);
    return $u;
  }
}

