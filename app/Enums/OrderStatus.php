<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PENDING_PAYMENT = 'pending_payment';
    case PENDING_VERIFICATION = 'pending_verification';
    case PROCESSING = 'processing';
    case IN_PRODUCTION = 'in_production';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case ON_HOLD = 'on_hold';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PENDING_PAYMENT => 'Pending Payment',
            self::PENDING_VERIFICATION => 'Pending Verification',
            self::PROCESSING => 'Processing',
            self::IN_PRODUCTION => 'In Production',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::COMPLETED => 'Completed',
            self::ON_HOLD => 'On Hold',
            self::CANCELLED => 'Cancelled',
        };
    }
}
