<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use LaundryLoop\Config\Database;
use LaundryLoop\Config\Settings;
use LaundryLoop\Controllers\BookingsController;
use LaundryLoop\Middleware\SessionAuthMiddleware;
use LaundryLoop\Middleware\JsonMiddleware;
use LaundryLoop\Repository\BookingRepository;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

session_start();

// ─── 1. Load config (.env) ────────────────────────────────────────────────────

Settings::load(__DIR__ . '/../.env');

// ─── 2. Build DI container ────────────────────────────────────────────────────

$builder = new ContainerBuilder();
$builder->addDefinitions([

    // Logger (mirrors ILogger in .NET)
    \Psr\Log\LoggerInterface::class => function () {
        $log = new Logger('laundryloop');
        $log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        return $log;
    },

    // PDO connection
    \PDO::class => fn() => Database::connect(),

    // Repository
    BookingRepository::class => fn(\DI\Container $c)
        => new BookingRepository($c->get(\PDO::class)),

    // Controller
    BookingsController::class => fn(\DI\Container $c)
        => new BookingsController(
            $c->get(BookingRepository::class),
            $c->get(\Psr\Log\LoggerInterface::class)
        ),
]);

$container = $builder->build();

// ─── 3. Create Slim app ───────────────────────────────────────────────────────

AppFactory::setContainer($container);
$app = AppFactory::create();

// Global middleware
$app->addBodyParsingMiddleware();      // parses JSON request bodies — like [FromBody] in .NET
$app->addRoutingMiddleware();
$app->add(new JsonMiddleware());

// Error handler
$app->addErrorMiddleware(
    displayErrorDetails: Settings::get('app.env') === 'development',
    logErrors: true,
    logErrorDetails: true
);

// ─── 4. CORS (allow the admin HTML page same as .NET app) ─────────────────────

$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin',  '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, DELETE, OPTIONS');
});

// ─── 5. Routes ────────────────────────────────────────────────────────────────
// Mirrors all routes from BookingsController.cs

$app->get('/api/config', [BookingsController::class, 'getConfig']);

// Public routes
$app->post('/api/bookings',                       [BookingsController::class, 'create']);
$app->get('/api/bookings/{id:[0-9]+}',            [BookingsController::class, 'getById']);
$app->get('/api/bookings/{id:[0-9]+}/cart-items', [BookingsController::class, 'getCartItems']);

// ─── Auth routes (login / logout / session check) ────────────────────────────

$app->post('/api/auth/login', function ($request, $response) {
    $body = $request->getParsedBody();
    $username = trim($body['username'] ?? '');
    $password = $body['password'] ?? '';

    if ($username === '' || $password === '') {
        $response->getBody()->write(json_encode(['error' => 'Username and password are required']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $user = Settings::authenticate($username, $password);
    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'Invalid username or password']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $_SESSION['user'] = $user;
    session_regenerate_id(true);

    $response->getBody()->write(json_encode($user));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/auth/logout', function ($request, $response) {
    $_SESSION = [];
    session_destroy();
    $response->getBody()->write(json_encode(['message' => 'Logged out']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/auth/me', function ($request, $response) {
    if (empty($_SESSION['user'])) {
        $response->getBody()->write(json_encode(['error' => 'Not authenticated']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
    $response->getBody()->write(json_encode($_SESSION['user']));
    return $response->withHeader('Content-Type', 'application/json');
});

// Protected routes — require session auth
$app->group('', function ($group) {
    $group->get('/api/bookings',                          [BookingsController::class, 'getAll']);
    $group->patch('/api/bookings/{id:[0-9]+}/status',     [BookingsController::class, 'updateStatus']);
    $group->delete('/api/bookings/{id:[0-9]+}',           [BookingsController::class, 'delete']);
})->add(new SessionAuthMiddleware());

// ─── 6. Run ───────────────────────────────────────────────────────────────────

$app->run();