<?php

namespace App\Contracts;

interface SlotCapacityRepositoryInterface
{
    /**
     * Данные по одному слоту + его активные холдоы.
     *
     * @param int $id - ID слота
     * @return array{id:int, capacity:int, active_holds:int}|null
     */
    public function get(int $id): ?array;

    /**
     * Количество активных холдов для слота.
     *
     * @param int $slotId - ID слота
     * @return int
     */
    public function countHolds(int $slotId): int;

}
