<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

/**
 * Идея: базовый SlotsRepository умеет только читать из БД.
 * Отдельный CachedSlotsRepository оборачивает его и добавляет кеш + lock.
 * Сервис получает интерфейс репозитория и ему не обязательно знать про кеш.
 */
interface SlotRepositoryInterface
{
    /**
     * Геттер коллекции слотов из базы или кэша
     *
     * @return Collection
     */
    public function getCollection(): Collection;
}
