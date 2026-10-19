<?php

namespace App\Core;

class BookingQr
{
    public static function bookingCode($bookingId)
    {
        return 'BADMINTON-BOOKING-' . (int) $bookingId;
    }

    public static function imageUrl($bookingId, $size = 220)
    {
        $size = max(120, min(500, (int) $size));
        $code = rawurlencode(self::bookingCode($bookingId));
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . $code;
    }
}
