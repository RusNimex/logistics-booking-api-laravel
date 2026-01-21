<?php

namespace App\Contracts;


use App\Exceptions\SlotsExceptions;
use App\Models\Hold;

/**
 * Сервисный слой для создания холда в слоте.
 */
interface SlotHolderServiceInterface
{
    /**
     * Время жизни кеша для идемпотентности.
     */
    const int IDEMPOTENCY_TTL_MINUTES = 5;

    /**
     * Создаем холд на N минут и поддерживаем идемпотентность.
     *
     * @param int $slotId
     * @param string $idempotencyKey
     * @return Hold
     *
     * @throws SlotsExceptions
     */
    public function create(int $slotId, string $idempotencyKey): Hold;

}
