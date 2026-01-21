<?php

namespace App\Services;

use App\Contracts\SlotServiceCollectorInterface;
use App\Contracts\SlotRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Сервисная логика обработки слотов.
 */
class SlotServiceCollector implements SlotServiceCollectorInterface
{
    public function __construct(
        protected SlotRepositoryInterface $repository
    ) {}

    /**
     * Готовая коллекция слотов
     *
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->repository->getCollection();
    }

}
