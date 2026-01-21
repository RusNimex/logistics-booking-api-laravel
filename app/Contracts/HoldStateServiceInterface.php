<?php

namespace App\Contracts;

/**
 * Сервис для изменения статуса холда
 */
interface HoldStateServiceInterface
{
    /**
     * Подтверждение.
     * Меняет статус холда и уменьшает remaining у слота.
     *
     * @param int $holdId
     * @return bool
     */
    public function confirm(int $holdId): bool;

    /**
     * Отмена холда.
     * Здесь наоборот, remaining у слота увеличивается.
     *
     * @param int $holdId
     * @return bool
     */
    public function cancel(int $holdId): bool;

}
