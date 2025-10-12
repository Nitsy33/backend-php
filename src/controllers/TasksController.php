<?php
declare(strict_types=1);

class TasksController {
  private const ALLOWED_STATUS = ['NUEVA','EN_PROGRESO','EN_ESPERA','BLOQUEADA','COMPLETADA','CANCELADA'];
  private const ALLOWED_URGENCY = ['BAJA','MEDIA','ALTA','CRITICA'];
  private const ALLOWED_TYPE = ['SIMPLE','SEGUIMIENTO'];

  public static function my(): void {
    $auth = require_auth();
    $pdo = db();
    $rows = TaskRepo::findVisibleForUser($pdo, (int)$auth['sub']);
    json_out($rows);
  }

  public static function getOne(int $id): void {
    $auth = require_auth();
    $pdo = db();
    $task = TaskRepo::findOne($pdo, $id);
    if (!$task) json_out(['error'=>'No existe'], 404);

    // (opcional) validar visibilidad
    // - Si es asignada al user o al área del user
    $visible = false;
    if (!empty($task['assigned_to_user_id']) && (int)$task['assigned_to_user_id']===(int)$auth['sub']) {
      $visible = true;
    } else {
      // check área
      $areas = $auth['areas'] ?? [];
      $visible = in_array((int)$task['area_id'], array_map('intval', $areas), true);
    }
    if (!$visible) json_out(['error'=>'Forbidden'], 403);

    $task['comments'] = CommentRepo::listByTask($pdo, $id);
    $task['subtasks'] = TaskRepo::subtasksOf($pdo, $id);

    json_out($task);
  }

  public static function create(): void {
    $auth = require_auth();
    $in = read_json();

    $areaId = require_int($in['areaId'] ?? null, 'areaId');
    $title  = required_string($in['title'] ?? null, 'title');

    $assignedTo = $in['assignedToUserId'] ?? null;
    $description = $in['description'] ?? null;
    $taskType = require_enum($in['taskType'] ?? 'SIMPLE', self::ALLOWED_TYPE, 'taskType');
    $urgency  = require_enum($in['urgency'] ?? 'MEDIA', self::ALLOWED_URGENCY, 'urgency');
    $dueAt    = pg_iso_datetime($in['dueAt'] ?? null);

    $pdo = db();

    // (recomendado) asegurarse que es coordinador del área
    if (!AreaRepo::isCoordinator($pdo, $areaId, (int)$auth['sub'])) {
      json_out(['error'=>'Solo el coordinador puede crear tareas en esta área'], 403);
    }

    $task = TaskRepo::create($pdo, [
      'area_id' => $areaId,
      'assigned_to_user_id' => $assignedTo,
      'assigned_by_user_id' => (int)$auth['sub'],
      'title' => $title,
      'description' => $description,
      'task_type' => $taskType,
      'urgency' => $urgency,
      'due_at' => $dueAt
    ]);
    json_out($task, 201);
  }

  public static function updateStatus(int $id): void {
    $auth = require_auth();
    $in = read_json();
    $status = require_enum($in['status'] ?? null, self::ALLOWED_STATUS, 'status');
    $note = trim((string)($in['note'] ?? ''));

    $pdo = db();
    $pdo->beginTransaction();
    $current = TaskRepo::findOne($pdo, $id);
    if (!$current) { $pdo->rollBack(); json_out(['error'=>'No existe'], 404); }

    // Permiso: si es asignada a mí, o del área que integro
    $allowed = ((int)$current['assigned_to_user_id'] === (int)$auth['sub'])
      || in_array((int)$current['area_id'], array_map('intval', $auth['areas'] ?? []), true);

    if (!$allowed) { $pdo->rollBack(); json_out(['error'=>'Forbidden'], 403); }

    $updated = TaskRepo::updateStatus($pdo, $id, $status);
    if (!$updated) { $pdo->rollBack(); json_out(['error'=>'No existe'], 404); }

    // Historial
    $h = $pdo->prepare("INSERT INTO task_status_history(task_id, changed_by, from_status, to_status, note)
                        VALUES (:t,:u,:from,:to,:n)");
    $h->execute([
      ':t'=>$id, ':u'=>(int)$auth['sub'],
      ':from'=>$current['status'], ':to'=>$status, ':n'=>$note ?: null
    ]);

    $pdo->commit();
    json_out(['ok'=>true, 'status'=>$status]);
  }
}
