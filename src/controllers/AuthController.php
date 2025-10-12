<?php
declare(strict_types=1);

class AuthController {
  public static function login(): void {
    $in = read_json();
    $email = required_string($in['email'] ?? null, 'email');

    $pdo = db();
    $user = UserRepo::findByEmail($pdo, $email);
    if (!$user) json_out(['error'=>'Usuario no encontrado'], 404);
    $areas = UserRepo::userAreas($pdo, (int)$user['id']);

    $token = jwt_sign(['sub'=>$user['id'], 'email'=>$user['email'], 'areas'=>$areas], 60*60*24*2);
    $user['areas'] = $areas;

    json_out(['token'=>$token, 'user'=>$user]);
  }

  public static function me(): void {
    $auth = require_auth();
    $pdo  = db();
    $me = UserRepo::me($pdo, (int)$auth['sub']);
    if (!$me) json_out(['error'=>'Usuario no encontrado'], 404);
    json_out($me);
  }
}
