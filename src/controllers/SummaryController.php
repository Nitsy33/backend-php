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

    // 2️⃣ Permitir solo al coordinador
    if ((int)$area['coordinator_id'] !== (int)$auth['sub']) {
      json_out(['error' => 'Solo el coordinador puede ver estas estadísticas'], 403);
    }

    // 3️⃣ Determinar si existe tabla intermedia area_member
    $hasAreaMember = false;
    try {
      $chk = $pdo->query("SELECT 1 FROM area_member LIMIT 1");
      $hasAreaMember = $chk !== false;
    } catch (Throwable $e) {
      $hasAreaMember = false;
    }

    // 4️⃣ Condición dinámica: incluye tareas del área y de sus miembros
    if ($hasAreaMember) {
      $scope = "
        (area_id = :id OR assigned_to_user_id IN (
          SELECT user_id FROM area_member WHERE area_id = :id
        ))
      ";
    } else {
      $scope = "
        (area_id = :id OR assigned_to_user_id IN (
          SELECT id FROM user_account WHERE area_id = :id
        ))
      ";
    }

    // 5️⃣ Total de tareas (del área o asignadas a miembros)
    $st = $pdo->prepare("SELECT COUNT(*) FROM task WHERE $scope");
    $st->execute([':id' => $areaId]);
    $total = (int)$st->fetchColumn();

    // 6️⃣ Agrupar por estado
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

    // 7️⃣ Agrupar por urgencia
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

    // 8️⃣ Agrupar por tipo
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

    // 9️⃣ Últimos comentarios (de cualquier miembro del área)
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
      WHERE (t.area_id = :id OR t.assigned_to_user_id IN (
        SELECT " . ($hasAreaMember ? "user_id FROM area_member" : "id FROM user_account") . " WHERE area_id = :id
      ))
      ORDER BY tc.created_at DESC
      LIMIT 5
    ");
    $st->execute([':id' => $areaId]);
    $lastComments = $st->fetchAll(PDO::FETCH_ASSOC);

    // 🔟 Cálculo de porcentaje de completadas
    $completed = $porEstado['COMPLETADA'] ?? 0;
    $progressPercent = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

    // ✅ Salida final
    $out = [
      'area'             => $area['name'],
      'total'            => $total,
      'por_estado'       => $porEstado,
      'por_urgencia'     => $porUrgencia,
      'por_tipo'         => $porTipo,
      'progress_percent' => $progressPercent,
      'last_comments'    => $lastComments
    ];

    json_out($out);
  }
}
