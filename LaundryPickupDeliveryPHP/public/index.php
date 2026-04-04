<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use LaundryLoop\Config\Database;
use LaundryLoop\Config\Settings;
use LaundryLoop\Controllers\BookingsController;
use LaundryLoop\Middleware\BasicAuthMiddleware;
use LaundryLoop\Middleware\JsonMiddleware;
use LaundryLoop\Repository\BookingRepository;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

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

// Protected routes — require Basic Auth (mirrors [Authorize] in .NET)
$app->group('', function ($group) {
    $group->get('/api/bookings',                          [BookingsController::class, 'getAll']);
    $group->patch('/api/bookings/{id:[0-9]+}/status',     [BookingsController::class, 'updateStatus']);
    $group->delete('/api/bookings/{id:[0-9]+}',           [BookingsController::class, 'delete']);
})->add(new BasicAuthMiddleware());

// ─── 6. Run ───────────────────────────────────────────────────────────────────

$app->run();