<?php

namespace App\Enums;

/**
 * Trip lifecycle statuses (Spec §10.4).
 */
enum TripStatus: string
{
    case NEW = 'new';
    case CONFIRMED = 'confirmed';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case DOCS_SENT = 'docs_sent';
    case AWAIT_PAY = 'await_pay';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    /** @return array<string,string> value => human-readable label */
    public static function labels(): array
    {
        return [
            'new' => __('trips.status.new'),
            'confirmed' => __('trips.status.confirmed'),
            'in_progress' => __('trips.status.in_progress'),
            'completed' => __('trips.status.completed'),
            'docs_sent' => __('trips.status.docs_sent'),
            'await_pay' => __('trips.status.await_pay'),
            'paid' => __('trips.status.paid'),
            'cancelled' => __('trips.status.cancelled'),
        ];
    }

    public static function options(): array
    {
        return self::labels();
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }
}
