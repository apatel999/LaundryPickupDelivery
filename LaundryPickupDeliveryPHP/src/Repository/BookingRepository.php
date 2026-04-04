<?php

declare(strict_types=1);

namespace LaundryLoop\Repository;

use LaundryLoop\Models\Booking;
use LaundryLoop\Models\CartItem;
use PDO;

class BookingRepository
{
    public function __construct(private readonly PDO $db) {}

    // ─── CREATE ────────────────────────────────────────────────────────────────

    public function insert(Booking $booking): Booking
    {
        $sql = "
            INSERT INTO Bookings (
                Reference, ApartmentName, ApartmentAddress, UnitNumber, BuzzerCode,
                Phone, Email, SlotId, SlotDay, SlotPeriod, PickupTime, DeliveryTime,
                LaundrySize, TotalCost, Notes, Status, CreatedAt, UpdatedAt
            ) VALUES (
                :reference, :apartmentName, :apartmentAddress, :unitNumber, :buzzerCode,
                :phone, :email, :slotId, :slotDay, :slotPeriod, :pickupTime, :deliveryTime,
                :laundrySize, :totalCost, :notes, :status, :createdAt, :updatedAt
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':reference'        => $booking->reference,
            ':apartmentName'    => $booking->apartmentName,
            ':apartmentAddress' => $booking->apartmentAddress,
            ':unitNumber'       => $booking->unitNumber,
            ':buzzerCode'       => $booking->buzzerCode,
            ':phone'            => $booking->phone,
            ':email'            => $booking->email,
            ':slotId'           => $booking->slotId,
            ':slotDay'          => $booking->slotDay,
            ':slotPeriod'       => $booking->slotPeriod,
            ':pickupTime'       => $booking->pickupTime,
            ':deliveryTime'     => $booking->deliveryTime,
            ':laundrySize'      => $booking->laundrySize,
            ':totalCost'        => $booking->totalCost,
            ':notes'            => $booking->notes,
            ':status'           => $booking->status,
            ':createdAt'        => $booking->createdAt,
            ':updatedAt'        => $booking->updatedAt,
        ]);

        $booking->id = (int) $this->db->lastInsertId();
        return $booking;
    }

    // ─── CREATE CartItem ───────────────────────────────────────────────────────

    public function insertCartItem(CartItem $item): CartItem
    {
        $sql = "
            INSERT INTO CartItems (BookingId, ItemType, ItemName, Description, Price)
            VALUES (:bookingId, :itemType, :itemName, :description, :price)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':bookingId'   => $item->bookingId,
            ':itemType'    => $item->itemType,
            ':itemName'    => $item->itemName,
            ':description' => $item->description,
            ':price'       => $item->price,
        ]);

        $item->id = (int) $this->db->lastInsertId();
        return $item;
    }

    // ─── READ CartItems ────────────────────────────────────────────────────────

    public function getCartItems(int $bookingId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM CartItems WHERE BookingId = :bookingId ORDER BY Id'
        );
        $stmt->execute([':bookingId' => $bookingId]);

        return array_map(
            fn(array $row) => CartItem::fromRow($row)->toArray(),
            $stmt->fetchAll()
        );
    }

    // ─── READ single ───────────────────────────────────────────────────────────

    public function getById(int $id): ?Booking
    {
        $stmt = $this->db->prepare('SELECT * FROM Bookings WHERE Id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Booking::fromRow($row) : null;
    }

    // ─── READ list (filter + sort + pagination) ────────────────────────────────

    public function getAll(
        int     $page,
        int     $pageSize,
        ?string $status,
        ?string $sortBy,
        ?string $sortDir,
        string  $search = '',
        ?string $date = null
    ): array {
        // Whitelist sort columns — prevents SQL injection
        $allowedSort = [
            'reference'        => 'Reference',
            'apartmentname'    => 'ApartmentName',
            'apartmentaddress' => 'ApartmentAddress',
            'unitnumber'       => 'UnitNumber',
            'slotday'          => 'SlotDay',
            'pickuptime'       => 'PickupTime',
            'status'           => 'Status',
            'createdat'        => 'CreatedAt',
        ];

        $orderCol = $allowedSort[strtolower($sortBy ?? '')] ?? 'CreatedAt';
        $orderDir = strtolower($sortDir ?? '') === 'asc' ? 'ASC' : 'DESC';
        $offset   = ($page - 1) * $pageSize;

        $where  = '';
        $params = [];
        $conditions = [];

        if (!empty($status)) {
            $conditions[]       = 'Status = :status';
            $params[':status']  = $status;
        }

        if ($search !== '') {
            $conditions[]       = '(Phone LIKE :search OR ApartmentAddress LIKE :search)';
            $params[':search']  = '%' . $search . '%';
        }

        if (!empty($date)) {
            $conditions[]     = 'DATE(PickupTime) = :date';
            $params[':date']  = $date;
        }

        if (!empty($conditions)) {
            $where = 'WHERE ' . implode(' AND ', $conditions);
        }

        // Count query
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM Bookings $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Data query — MySQL uses LIMIT / OFFSET (not OFFSET…FETCH like SQL Server)
        $dataStmt = $this->db->prepare("
            SELECT * FROM Bookings $where
            ORDER BY $orderCol $orderDir
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $val) {
            $dataStmt->bindValue($key, $val);
        }
        $dataStmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $dataStmt->execute();

        $items = array_map(
            fn(array $row) => Booking::fromRow($row)->toResponse(),
            $dataStmt->fetchAll()
        );

        return ['total' => $total, 'items' => $items];
    }

    // ─── UPDATE status ─────────────────────────────────────────────────────────

    public function updateStatus(int $id, string $status): ?Booking
    {
        $stmt = $this->db->prepare("
            UPDATE Bookings
            SET Status = :status, UpdatedAt = :updatedAt
            WHERE Id = :id
        ");
        $stmt->execute([
            ':status'    => $status,
            ':updatedAt' => gmdate('Y-m-d H:i:s'),
            ':id'        => $id,
        ]);

        return $this->getById($id);
    }

    // ─── DELETE ────────────────────────────────────────────────────────────────

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM Bookings WHERE Id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
