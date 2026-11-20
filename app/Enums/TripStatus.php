<?php

namespace App\Enums;

/**
 * Статусы жизненного цикла рейса (ТЗ §10.4).
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

    /** @return array<string,string> value => человекочитаемый label */
    public static function labels(): array
    {
        return [
            'new' => 'Новый',
            'confirmed' => 'Подтверждён',
            'in_progress' => 'В пути',
            'completed' => 'Выполнен',
            'docs_sent' => 'Документы отправлены',
            'await_pay' => 'Ожидается оплата',
            'paid' => 'Оплачен',
            'cancelled' => 'Отменён',
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
