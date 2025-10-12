<?php
declare(strict_types=1);

class TaskRepo {
  public static function findVisibleForUser(PDO $pdo, int $userId): array {
    $sql = "
      WITH mis_areas AS (SELECT area_id FROM area_member WHERE user_id = :u)
      SELECT t.*
      FROM task t
      LEFT JOIN mis_areas ma ON ma.area_id = t.area_id
      WHERE t.assigned_to_user_id = :u
         OR (t.assigned_to_user_id IS NULL AND ma.area_id IS NOT NULL)
      ORDER BY (t.assigned_to_user_id IS NULL) ASC, urgency DESC, COALESCE(due_at, created_at) ASC";
    $st = $pdo->prepare($sql);
    $st->execute([':u'=>$userId]);
    return $st->fetchAll();
  }

  public static function findOne(PDO $pdo, int $taskId): ?array {
    $t = $pdo->prepare("SELECT * FROM task WHERE id=:id");
    $t->execute([':id'=>$taskId]);
    $task = $t->fetch();
    return $task ?: null;
  }

  public static function create(PDO $pdo, array $data): array {
    $st = $pdo->prepare("
      INSERT INTO task(area_id, assigned_to_user_id, assigned_by_user_id, title, description, task_type, urgency, status, due_at)
      VALUES (:a, :to, :by, :t, :d, :type, :urg, 'NUEVA', :due)
      RETURNING *");
    $st->execute([
      ':a'=>$data['area_id'],
      ':to'=>$data['assigned_to_user_id'],
      ':by'=>$data['assigned_by_user_id'],
      ':t'=>$data['title'],
      ':d'=>$data['description'],
      ':type'=>$data['task_type'],
      ':urg'=>$data['urgency'],
      ':due'=>$data['due_at']
    ]);
    return $st->fetch();
  }

  public static function updateStatus(PDO $pdo, int $taskId, string $status): ?array {
    $st = $pdo->prepare("UPDATE task SET status=:s, updated_at=now() WHERE id=:id RETURNING *");
    $st->execute([':s'=>$status, ':id'=>$taskId]);
    $task = $st->fetch();
    return $task ?: null;
  }

  public static function subtasksOf(PDO $pdo, int $taskId): array {
    $s = $pdo->prepare("SELECT * FROM task_subtask WHERE task_id=:id ORDER BY position ASC");
    $s->execute([':id'=>$taskId]);
    return $s->fetchAll();
  }
}
