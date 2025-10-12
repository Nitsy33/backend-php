<?php
declare(strict_types=1);

class CommentsController {
  public static function add(int $taskId): void {
    $auth = require_auth();
    $in = read_json();
    $body = required_string($in['body'] ?? null, 'body');

    $pdo = db();
    $task = TaskRepo::findOne($pdo, $taskId);
    if (!$task) json_out(['error'=>'No existe la tarea'], 404);

    // permiso: miembro del área o asignatario
    $allowed = ((int)$task['assigned_to_user_id'] === (int)$auth['sub'])
      || in_array((int)$task['area_id'], array_map('intval', $auth['areas'] ?? []), true);
    if (!$allowed) json_out(['error'=>'Forbidden'], 403);

    $c = CommentRepo::add($pdo, $taskId, (int)$auth['sub'], $body);
    json_out($c, 201);
  }
}
