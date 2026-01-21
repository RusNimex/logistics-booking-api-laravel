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
