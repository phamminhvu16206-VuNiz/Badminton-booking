ALTER TABLE bookings
    ADD COLUMN cancellation_status ENUM('none', 'pending', 'refunded') NOT NULL DEFAULT 'none' AFTER status,
    ADD COLUMN cancellation_reason TEXT NULL AFTER cancellation_status,
    ADD COLUMN cancellation_requested_at DATETIME NULL AFTER cancellation_reason,
    ADD COLUMN refunded_at DATETIME NULL AFTER cancellation_requested_at,
    ADD INDEX idx_bookings_cancellation_status (cancellation_status, cancellation_requested_at);
