<?php

namespace App\Enums;

enum PrintletStatus: string
{
    case PENDING_VERIFICATION = 'pending_verification';
    case ACCEPTED = 'accepted';
    case PAYMENT_REQUIRED = 'payment_required';
    case IN_PRODUCTION = 'in_production';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_VERIFICATION => 'Pending Verification',
            self::ACCEPTED => 'Accepted',
            self::PAYMENT_REQUIRED => 'Payment Required',
            self::IN_PRODUCTION => 'In Production',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }
}
