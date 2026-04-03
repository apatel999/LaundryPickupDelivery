<?php

declare(strict_types=1);

namespace LaundryLoop\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Ensures all responses carry Content-Type: application/json.
 */
class JsonMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
