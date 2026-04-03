<?php

declare(strict_types=1);

namespace LaundryLoop\Controllers;

use LaundryLoop\Config\Settings;
use LaundryLoop\Models\Booking;
use LaundryLoop\Models\CartItem;
use LaundryLoop\Repository\BookingRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class BookingsController
{
    public function __construct(
        private readonly BookingRepository $repo,
        private readonly LoggerInterface   $logger
    ) {}

    // ─── POST /api/bookings ────────────────────────────────────────────────────

    public function create(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        if (!is_array($body)) {
            return $this->json($response, ['error' => 'Invalid JSON body'], 400);
        }

        // Validate required fields — mirrors [Required] annotations on CreateBookingRequest
        $required = [
            'apartmentName', 'apartmentAddress', 'unitNumber',
            'phone', 'slotId', 'slotDay', 'slotPeriod',
            'pickupTime', 'deliveryTime', 'laundrySize', 'totalCost',
        ];

        $errors = [];
        foreach ($required as $field) {
            if (empty($body[$field]) && $body[$field] !== '0') {
                $errors[$field] = "$field is required";
            }
        }

        if (!empty($body['email']) && !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email address';
        }

        if (!empty($errors)) {
            return $this->json($response, ['errors' => $errors], 400);
        }

        $now = gmdate('Y-m-d H:i:s');

        $booking                  = new Booking();
        $booking->reference        = $this->generateReference();
        $booking->apartmentName    = trim($body['apartmentName']);
        $booking->apartmentAddress = trim($body['apartmentAddress']);
        $booking->unitNumber       = trim($body['unitNumber']);
        $booking->buzzerCode       = isset($body['buzzerCode']) ? trim($body['buzzerCode']) : null;
        $booking->phone            = trim($body['phone']);
        $booking->email            = isset($body['email']) ? strtolower(trim($body['email'])) : null;
        $booking->slotId           = trim($body['slotId']);
        $booking->slotDay          = trim($body['slotDay']);
        $booking->slotPeriod       = trim($body['slotPeriod']);
        $booking->pickupTime       = trim($body['pickupTime']);
        $booking->deliveryTime     = trim($body['deliveryTime']);
        $booking->laundrySize      = trim($body['laundrySize']);
        $booking->totalCost        = (float) $body['totalCost'];
        $booking->notes            = isset($body['notes']) ? trim($body['notes']) : null;
        $booking->status           = 'Pending';
        $booking->createdAt        = $now;
        $booking->updatedAt        = $now;

        $this->repo->insert($booking);

        // Insert cart items
        $cartItems = $body['cartItems'] ?? [];
        foreach ($cartItems as $itemData) {
            $item              = new CartItem();
            $item->bookingId   = $booking->id;
            $item->itemType    = $itemData['itemType']    ?? '';
            $item->itemName    = $itemData['itemName']    ?? '';
            $item->description = $itemData['description'] ?? null;
            $item->price       = (float) ($itemData['price'] ?? 0);
            $this->repo->insertCartItem($item);
        }

        $this->logger->info('Booking created: {ref} — {apt} unit {unit}', [
            'ref'  => $booking->reference,
            'apt'  => $booking->apartmentName,
            'unit' => $booking->unitNumber,
        ]);

        // 201 Created with Location header — mirrors CreatedAtAction()
        $responseData = $booking->toResponse();
        return $this->json(
            $response->withHeader('Location', "/api/bookings/{$booking->id}"),
            $responseData,
            201
        );
    }

    // ─── GET /api/bookings/{id} ────────────────────────────────────────────────

    public function getById(Request $request, Response $response, array $args): Response
    {
        $booking = $this->repo->getById((int) $args['id']);

        if ($booking === null) {
            return $this->json($response, ['error' => 'Not found'], 404);
        }

        return $this->json($response, $booking->toResponse());
    }

    // ─── GET /api/bookings?page=1&pageSize=50&status=Pending&sortBy=createdAt&sortDir=desc
    // [Authorize] — protected by BasicAuthMiddleware applied at route level

    public function getAll(Request $request, Response $response): Response
    {
        $params   = $request->getQueryParams();
        $page     = max(1, (int) ($params['page']     ?? 1));
        $pageSize = max(1, min(200, (int) ($params['pageSize'] ?? 50)));
        $status   = $params['status']  ?? null;
        $sortBy   = $params['sortBy']  ?? 'createdAt';
        $sortDir  = $params['sortDir'] ?? 'desc';

        $result = $this->repo->getAll($page, $pageSize, $status, $sortBy, $sortDir);

        return $this->json($response, [
            'total'    => $result['total'],
            'page'     => $page,
            'pageSize' => $pageSize,
            'items'    => $result['items'],
        ]);
    }

    // ─── PATCH /api/bookings/{id}/status ──────────────────────────────────────
    // Body: { "status": "PickedUp" }
    // [Authorize] — protected by BasicAuthMiddleware applied at route level

    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        $allowed = ['Pending', 'Confirmed', 'PickedUp', 'Delivered', 'Cancelled'];
        $body    = $request->getParsedBody();
        $status  = $body['status'] ?? '';

        if (!in_array($status, $allowed, true)) {
            return $this->json($response, [
                'error' => 'Invalid status. Allowed: ' . implode(', ', $allowed),
            ], 400);
        }

        $booking = $this->repo->updateStatus((int) $args['id'], $status);

        if ($booking === null) {
            return $this->json($response, ['error' => 'Not found'], 404);
        }

        return $this->json($response, $booking->toResponse());
    }

    // ─── DELETE /api/bookings/{id} ─────────────────────────────────────────────
    // [Authorize] — protected by BasicAuthMiddleware applied at route level

    public function delete(Request $request, Response $response, array $args): Response
    {
        $deleted = $this->repo->delete((int) $args['id']);
        return $deleted
            ? $response->withStatus(204)
            : $this->json($response, ['error' => 'Not found'], 404);
    }

    // ─── GET /api/bookings/{id}/cart-items ────────────────────────────────────

    public function getCartItems(Request $request, Response $response, array $args): Response
    {
        $items = $this->repo->getCartItems((int) $args['id']);
        return $this->json($response, $items);
    }

    // ─── GET /api/config ──────────────────────────────────────────────────────

    public function getConfig(Request $request, Response $response): Response
    {
        return $this->json($response, [
            'baseUrl' => Settings::get('app.base_url'),
        ]);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function generateReference(): string
    {
        return 'LLP-' . random_int(100_000, 999_999);
    }

    private function json(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
