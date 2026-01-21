<?php

namespace App\Repository;

use App\Contracts\SlotRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Коллекция слотов из базы данных.
 *
 * Имеет обертку для кеширования @see CachedSlotRepository
 */
class DbSlotRepository implements SlotRepositoryInterface
{
    /**
     * Данные из таблицы slots
     *
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return DB::table('slots')
            ->select(['id', 'capacity', 'remaining'])
            ->get();
    }

}
