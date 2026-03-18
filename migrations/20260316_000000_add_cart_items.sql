-- Migration: Add CartItems table for itemized billing and update columns
-- Run this after the initial setup-database.sql to add shopping cart functionality

USE LaundryLoopdDb;
GO

-- Rename Services to LaundrySize (keeping for quick access, but can be derived from CartItems)
EXEC sp_rename 'Bookings.Services', 'LaundrySize', 'COLUMN';
-- Note: Addons column removed - now derived from CartItems where ItemType = 'addon'
ALTER TABLE Bookings ADD TotalCost DECIMAL(10,2) NOT NULL DEFAULT 0;
GO

-- Create CartItems table for detailed item breakdown
CREATE TABLE CartItems (
    Id          INT           IDENTITY(1,1) PRIMARY KEY,
    BookingId   INT           NOT NULL,
    ItemType    NVARCHAR(50)  NOT NULL, -- 'laundry', 'addon', 'delivery'
    ItemName    NVARCHAR(100) NOT NULL,
    Description NVARCHAR(200) NULL,
    Price       DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (BookingId) REFERENCES Bookings(Id) ON DELETE CASCADE
);
GO

-- Add index for performance
CREATE INDEX IX_CartItems_BookingId ON CartItems (BookingId);
GO