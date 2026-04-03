-- LaundryLoop — MySQL Database Setup
-- Equivalent of setup-database.sql + 20260316_000000_add_cart_items.sql
-- Run once before starting the API.

CREATE TABLE IF NOT EXISTS Bookings (
    Id               INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Reference        VARCHAR(20)    NOT NULL,
    ApartmentName    VARCHAR(100)   NULL,
    ApartmentAddress VARCHAR(150)   NOT NULL,
    UnitNumber       VARCHAR(20)    NULL,
    BuzzerCode       VARCHAR(50)    NULL,
    Phone            VARCHAR(20)    NOT NULL,
    Email            VARCHAR(200)   NULL,
    SlotId           VARCHAR(20)    NULL,
    SlotDay          VARCHAR(50)    NULL,
    SlotPeriod       VARCHAR(50)    NULL,
    PickupTime       VARCHAR(50)    NOT NULL,
    DeliveryTime     VARCHAR(100)   NOT NULL,
    LaundrySize      VARCHAR(200)   NOT NULL,
    TotalCost        DECIMAL(10,2)  NOT NULL DEFAULT 0,
    Notes            VARCHAR(1000)  NULL,
    Status           VARCHAR(30)    NOT NULL DEFAULT 'Pending',
    CreatedAt        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IX_Bookings_Reference ON Bookings (Reference);
CREATE        INDEX IX_Bookings_Phone     ON Bookings (Phone);
CREATE        INDEX IX_Bookings_Status    ON Bookings (Status);
CREATE        INDEX IX_Bookings_CreatedAt ON Bookings (CreatedAt DESC);

CREATE TABLE IF NOT EXISTS CartItems (
    Id          INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    BookingId   INT           NOT NULL,
    ItemType    VARCHAR(50)   NOT NULL,   -- 'laundry', 'addon', 'delivery'
    ItemName    VARCHAR(100)  NOT NULL,
    Description VARCHAR(200)  NULL,
    Price       DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (BookingId) REFERENCES Bookings(Id) ON DELETE CASCADE
);

CREATE INDEX IX_CartItems_BookingId ON CartItems (BookingId);