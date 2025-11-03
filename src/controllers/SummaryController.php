<?php
declare(strict_types=1);

class SummaryController {
  public static function area(int $areaId): void {
    $auth = require_auth();
    $pdo = db();

    // 1️⃣ Verificar área
    $st = $pdo->prepare("SELECT id, name, coordinator_id FROM area WHERE id = :id");
    $st->execute([':id' => $areaId]);
    $area = $st->fetch(PDO::FETCH_ASSOC);
    if (!$area) json_out(['error' => 'Área no existe'], 404);

    // 2️⃣ Solo el coordinador puede acceder
    if ((int)$area['coordinator_id'] !== (int)$auth['sub']) {
      json_out(['error' => 'Solo el coordinador puede ver estas estadísticas'], 403);
    }

    // 3️⃣ Base SQL reutilizable
    $scope = "
      (area_id = :id OR assigned_to IN (
        SELECT id FROM user_account WHERE area_id = :id
      ))
    ";

    // 4️⃣ Total
    $st = $pdo->prepare("SELECT COUNT(*) FROM task WHERE $scope");
    $st->execute([':id' => $areaId]);
    $total = (int)$st->fetchColumn();

    // 5️⃣ Por estado
    $st = $pdo->prepare("
      SELECT status, COUNT(*) AS c
      FROM task
      WHERE $scope
      GROUP BY status
    ");
    $st->execute([':id' => $areaId]);
    $porEstado = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
      $porEstado[$row['status']] = (int)$row['c'];
    }

    // 6️⃣ Por urgencia
    $st = $pdo->prepare("
      SELECT urgency, COUNT(*) AS c
      FROM task
      WHERE $scope
      GROUP BY urgency
    ");
    $st->execute([':id' => $areaId]);
    $porUrgencia = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
      $porUrgencia[$row['urgency']] = (int)$row['c'];
    }

    // 7️⃣ Por tipo
    $st = $pdo->prepare("
      SELECT task_type, COUNT(*) AS c
      FROM task
      WHERE $scope
      GROUP BY task_type
    ");
    $st->execute([':id' => $areaId]);
    $porTipo = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
      $porTipo[$row['task_type']] = (int)$row['c'];
    }

    // 8️⃣ Últimos comentarios (de todos los usuarios del área)
    $st = $pdo->prepare("
      SELECT 
        tc.id,
        tc.task_id,
        t.title AS task_title,
        tc.body,
        tc.created_at,
        ua.name AS author_name
      FROM task_comment tc
      JOIN task t ON t.id = tc.task_id
      JOIN user_account ua ON ua.id = tc.author_id
      WHERE $scope
      ORDER BY tc.created_at DESC
      LIMIT 5
    ");
    $st->execute([':id' => $areaId]);
    $lastComments = $st->fetchAll(PDO::FETCH_ASSOC);

    // 9️⃣ Salida final
    $out = [
      'area'         => $area['name'],
      'total'        => $total,
      'por_estado'   => $porEstado,
      'por_urgencia' => $porUrgencia,
      'por_tipo'     => $porTipo,
      'last_comments'=> $lastComments,
    ];

    json_out($out);
  }
}
