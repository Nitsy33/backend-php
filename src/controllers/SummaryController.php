<?php
declare(strict_types=1);

class SummaryController {
  public static function area(int $areaId): void {
    $auth = require_auth();
    $pdo = db();

    // Verificar que el área existe
    $st = $pdo->prepare("SELECT id, name, coordinator_id FROM area WHERE id = :id");
    $st->execute([':id' => $areaId]);
    $area = $st->fetch(PDO::FETCH_ASSOC);

    if (!$area) {
      json_out(['error' => 'Área no existe'], 404);
    }

    // Permitir solo al coordinador
    if ((int)$area['coordinator_id'] !== (int)$auth['sub']) {
      json_out(['error' => 'Solo el coordinador puede ver estas estadísticas'], 403);
    }

    // ✅ Contar todas las tareas del área, no solo las del usuario
    $st = $pdo->prepare("SELECT COUNT(*) FROM task WHERE area_id = :id");
    $st->execute([':id' => $areaId]);
    $total = (int)$st->fetchColumn();

    // ✅ Agrupar por estado (sin filtrar por usuario)
    $st = $pdo->prepare("
      SELECT status, COUNT(*) AS c
      FROM task
      WHERE area_id = :id
      GROUP BY status
    ");
    $st->execute([':id' => $areaId]);
    $porEstado = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
      $porEstado[$row['status']] = (int)$row['c'];
    }

    // ✅ Agrupar por urgencia
    $st = $pdo->prepare("
      SELECT urgency, COUNT(*) AS c
      FROM task
      WHERE area_id = :id
      GROUP BY urgency
    ");
    $st->execute([':id' => $areaId]);
    $porUrgencia = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
      $porUrgencia[$row['urgency']] = (int)$row['c'];
    }

    // ✅ Agrupar por tipo
    $st = $pdo->prepare("
      SELECT task_type, COUNT(*) AS c
      FROM task
      WHERE area_id = :id
      GROUP BY task_type
    ");
    $st->execute([':id' => $areaId]);
    $porTipo = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
      $porTipo[$row['task_type']] = (int)$row['c'];
    }

    // ✅ Últimos comentarios de cualquier miembro del área
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
      WHERE t.area_id = :id
      ORDER BY tc.created_at DESC
      LIMIT 5
    ");
    $st->execute([':id' => $areaId]);
    $lastComments = $st->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Salida final
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
