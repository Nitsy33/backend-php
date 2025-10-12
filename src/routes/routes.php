<?php
declare(strict_types=1);

function route_request() {
  $seg = path_segments();
  $m = method();

  // Health check
  if ($m === 'GET' && $seg === ['health']) {
    json_out(['ok'=>true, 'uptime'=>time()], 200);
  }

  // Auth
  if ($m === 'POST' && $seg === ['auth','login']) return AuthController::login();
  if ($m === 'GET'  && $seg === ['me'])          return AuthController::me();

  // Tasks
  if ($m === 'GET'  && $seg === ['tasks','my'])  return TasksController::my();
  if ($m === 'GET'  && count($seg)===2 && $seg[0]==='tasks' && ctype_digit($seg[1])) {
    return TasksController::getOne((int)$seg[1]);
  }
  if ($m === 'POST' && $seg === ['tasks'])       return TasksController::create();
  if ($m === 'PATCH' && count($seg)===3 && $seg[0]==='tasks' && ctype_digit($seg[1]) && $seg[2]==='status') {
    return TasksController::updateStatus((int)$seg[1]);
  }

  // Comments
  if ($m === 'POST' && count($seg)===3 && $seg[0]==='tasks' && ctype_digit($seg[1]) && $seg[2]==='comments') {
    return CommentsController::add((int)$seg[1]);
  }

  // Areas
  if ($m === 'GET' && $seg === ['areas','mine']) return AreasController::mine();
  if ($m === 'GET' && count($seg)===3 && $seg[0]==='areas' && ctype_digit($seg[1]) && $seg[2]==='members') {
    return AreasController::members((int)$seg[1]);
  }

  return null;
}
