<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/core/env.php';
require_once __DIR__ . '/../src/core/response.php';
require_once __DIR__ . '/../src/core/cors.php';
require_once __DIR__ . '/../src/core/router.php';
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/core/jwt.php';
require_once __DIR__ . '/../src/utils/validator.php';

require_once __DIR__ . '/../src/repositories/UserRepo.php';
require_once __DIR__ . '/../src/repositories/TaskRepo.php';
require_once __DIR__ . '/../src/repositories/CommentRepo.php';
require_once __DIR__ . '/../src/repositories/AreaRepo.php';

require_once __DIR__ . '/../src/middleware/auth.php';

require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/TasksController.php';
require_once __DIR__ . '/../src/controllers/CommentsController.php';
require_once __DIR__ . '/../src/controllers/AreasController.php';

require_once __DIR__ . '/../src/routes/routes.php';

try {
  // Manejo de OPTIONS ya se hace en cors.php (sale con 204)
  $res = route_request();
  if ($res === null) {
    json_out(['error' => 'Not found'], 404);
  }
} catch (Throwable $e) {
  json_out(['error' => 'Server error', 'detail' => $e->getMessage()], 500);
}
