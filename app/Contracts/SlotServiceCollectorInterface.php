<?php

namespace App\Contracts;


use Illuminate\Support\Collection;

/**
 * Сервисный слой для получения коллекции слотов.
 */
interface SlotServiceCollectorInterface
{
    /**
     * Коллекция слотов
     *
     * @return Collection
     */
    public function getCollection(): Collection;

}
