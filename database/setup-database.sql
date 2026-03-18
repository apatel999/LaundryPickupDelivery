-- LaundryLoop — Database setup script
-- Run this once against your SQL Server database before starting the API.
-- No migrations needed — just plain SQL.

CREATE DATABASE LaundryLoopdDb;
GO

USE LaundryLoopdDb;
GO

CREATE TABLE Bookings (
    Id               INT           IDENTITY(1,1) PRIMARY KEY,
    Reference        NVARCHAR(20)  NOT NULL,
    ApartmentName    NVARCHAR(100) NOT NULL,
    ApartmentAddress NVARCHAR(150) NOT NULL,
    UnitNumber       NVARCHAR(20)  NOT NULL,
    BuzzerCode       NVARCHAR(50)  NULL,
    Phone            NVARCHAR(20)  NOT NULL,
    Email            NVARCHAR(200) NULL,
    SlotId           NVARCHAR(20)  NOT NULL,
    SlotDay          NVARCHAR(50)  NOT NULL,
    SlotPeriod       NVARCHAR(50)  NOT NULL,
    PickupTime       NVARCHAR(50)  NOT NULL,
    DeliveryTime     NVARCHAR(100) NOT NULL,
    Services         NVARCHAR(200) NOT NULL,
    Notes            NVARCHAR(1000) NULL,
    Status           NVARCHAR(30)  NOT NULL DEFAULT 'Pending',
    CreatedAt        DATETIME2     NOT NULL DEFAULT GETUTCDATE(),
    UpdatedAt        DATETIME2     NOT NULL DEFAULT GETUTCDATE()
);
GO

-- Indexes for common lookups
CREATE UNIQUE INDEX IX_Bookings_Reference ON Bookings (Reference);
CREATE INDEX IX_Bookings_Phone     ON Bookings (Phone);
CREATE INDEX IX_Bookings_Status    ON Bookings (Status);
CREATE INDEX IX_Bookings_CreatedAt ON Bookings (CreatedAt DESC);
GO