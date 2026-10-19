ALTER TABLE bookings
    ADD COLUMN cancellation_bank_name VARCHAR(100) NULL AFTER cancellation_reason,
    ADD COLUMN cancellation_bank_account_name VARCHAR(150) NULL AFTER cancellation_bank_name,
    ADD COLUMN cancellation_bank_account_number VARCHAR(50) NULL AFTER cancellation_bank_account_name;
