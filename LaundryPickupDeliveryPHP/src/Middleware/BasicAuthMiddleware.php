<?php

declare(strict_types=1);

namespace LaundryLoop\Middleware;

use LaundryLoop\Config\Settings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * HTTP Basic Authentication middleware.
 * Mirrors BasicAuthenticationHandler.cs — protects admin routes (GET all, PATCH status, DELETE).
 */
class BasicAuthMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!str_starts_with($authHeader, 'Basic ')) {
            return $this->unauthorized();
        }

        $decoded = base64_decode(substr($authHeader, 6));
        [$user, $pass] = explode(':', $decoded, 2) + ['', ''];

        $validUser = Settings::get('auth.username');
        $validPass = Settings::get('auth.password');

        if (!hash_equals($validUser, $user) || !hash_equals($validPass, $pass)) {
            return $this->unauthorized();
        }

        return $handler->handle($request);
    }

    private function unauthorized(): ResponseInterface
    {
        $response = new Response(401);
        $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('WWW-Authenticate', 'Basic realm="LaundryLoop Admin"');
    }
}
