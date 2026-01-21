<?php

namespace App\Contracts;

use App\Models\Hold;

/**
 * Слой базы данных для холда.
 */
interface HoldCreateRepositoryInterface
{
    /**
     * Бронируем место в слоте на N минут путем создания записи в таблице holds.
     *
     * @param int $slotId
     * @return Hold
     */
    public function create(int $slotId): Hold;

}
