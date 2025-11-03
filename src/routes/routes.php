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
  
  if ($m === 'GET' && count($seg)===3 && $seg[0]==='tasks' && ctype_digit($seg[1]) && $seg[2]==='comments') {
    return CommentsController::list((int)$seg[1]);
  }

  // Areas
  if ($m === 'GET' && $seg === ['areas','mine']) return AreasController::mine();
  if ($m === 'GET' && count($seg)===3 && $seg[0]==='areas' && ctype_digit($seg[1]) && $seg[2]==='members') {
    return AreasController::members((int)$seg[1]);
  }

    // ✅ NUEVO: /areas/{areaId}/members/{userId}
  if ($m === 'GET'
      && count($seg)===4
      && $seg[0]==='areas'
      && ctype_digit($seg[1])
      && $seg[2]==='members'
      && ctype_digit($seg[3])) {
    return AreasController::member((int)$seg[1], (int)$seg[3]);
  }

  // Ya existente: /areas/{areaId}/members  (todos los miembros)
  if ($m === 'GET' && count($seg)===3 && $seg[0]==='areas' && ctype_digit($seg[1]) && $seg[2]==='members') {
    return AreasController::members((int)$seg[1]);
  }

    // Summary / Estadísticas de área
  if ($m === 'GET'
      && count($seg) === 3
      && $seg[0] === 'summary'
      && $seg[1] === 'area'
      && ctype_digit($seg[2])) {
    return SummaryController::area((int)$seg[2]);
  }

  // 👇 Fallback explícito
  error_log('ROUTER 404 method=' . $m . ' seg=' . json_encode($seg));
  return json_out(['error' => 'Not found', 'segments' => $seg, 'method' => $m], 404);
}
