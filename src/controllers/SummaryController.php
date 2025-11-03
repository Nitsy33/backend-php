<?php
declare(strict_types=1);

class SummaryController {
  public static function area(int $areaId): void {
    $auth = require_auth();
    $pdo = db();

    // 1️⃣ Verificar que el área exista
    $st = $pdo->prepare("SELECT id, name, coordinator_id FROM area WHERE id = :id");
    $st->execute([':id' => $areaId]);
    $area = $st->fetch(PDO::FETCH_ASSOC);
    if (!$area) json_out(['error' => 'Área no existe'], 404);

    // 2️⃣ Solo el coordinador puede acceder
    if ((int)$area['coordinator_id'] !== (int)$auth['sub']) {
      json_out(['error' => 'Solo el coordinador puede ver estas estadísticas'], 403);
    }

    // 3️⃣ Condición: tareas del área o asignadas a usuarios de la misma área
    $scope = "
      (t.area_id = :id OR t.assigned_to_user_id IN (
        SELECT ua.id FROM user_account ua WHERE ua.area_id = :id
      ))
    ";

    // 4️⃣ Total
    $st = $pdo->prepare("SELECT COUNT(*) FROM task t WHERE $scope");
    $st->execute([':id' => $areaId]);
    $total = (int)$st->fetchColumn();

    // 5️⃣ Por estado
    $st = $pdo->prepare("
      SELECT t.status, COUNT(*) AS c
      FROM task t
      WHERE $scope
      GROUP BY t.status
    ");
    $st->execute([':id' => $areaId]);
    $porEstado = [];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      $porEstado[$r['status']] = (int)$r['c'];
    }

    // 6️⃣ Por urgencia
    $st = $pdo->prepare("
      SELECT t.urgency, COUNT(*) AS c
      FROM task t
      WHERE $scope
      GROUP BY t.urgency
    ");
    $st->execute([':id' => $areaId]);
    $porUrgencia = [];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      $porUrgencia[$r['urgency']] = (int)$r['c'];
    }

    // 7️⃣ Por tipo
    $st = $pdo->prepare("
      SELECT t.task_type, COUNT(*) AS c
      FROM task t
      WHERE $scope
      GROUP BY t.task_type
    ");
    $st->execute([':id' => $areaId]);
    $porTipo = [];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      $porTipo[$r['task_type']] = (int)$r['c'];
    }

    // 8️⃣ Últimos comentarios
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

    // 9️⃣ Porcentaje de completadas
    $completed = $porEstado['COMPLETADA'] ?? 0;
    $progressPercent = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

    // 🔟 Resultado final
    json_out([
      'area'             => $area['name'],
      'total'            => $total,
      'por_estado'       => $porEstado,
      'por_urgencia'     => $porUrgencia,
      'por_tipo'         => $porTipo,
      'progress_percent' => $progressPercent,
      'last_comments'    => $lastComments
    ]);
  }
}
