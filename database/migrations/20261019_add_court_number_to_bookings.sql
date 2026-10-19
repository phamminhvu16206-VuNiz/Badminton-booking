ALTER TABLE bookings
    ADD COLUMN court_number TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER court_id,
    ADD INDEX idx_bookings_court_number_date_status (
        court_id,
        court_number,
        booking_date,
        status
    );
