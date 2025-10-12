<?php
declare(strict_types=1);

class CommentRepo {
  public static function listByTask(PDO $pdo, int $taskId): array {
    $c = $pdo->prepare("SELECT tc.*, ua.name AS author_name
                        FROM task_comment tc
                        JOIN user_account ua ON ua.id = tc.author_id
                        WHERE tc.task_id=:id
                        ORDER BY tc.created_at ASC");
    $c->execute([':id'=>$taskId]);
    return $c->fetchAll();
  }

  public static function add(PDO $pdo, int $taskId, int $authorId, string $body): array {
    $st = $pdo->prepare("INSERT INTO task_comment(task_id, author_id, body)
                         VALUES (:t, :u, :b) RETURNING *");
    $st->execute([':t'=>$taskId, ':u'=>$authorId, ':b'=>$body]);
    return $st->fetch();
  }
}
