<?php

namespace App\Http\Controllers\Slots;

use App\Contracts\HoldStateServiceInterface;
use App\Contracts\SlotHolderServiceInterface;
use App\Exceptions\SlotsExceptions;
use App\Http\Controllers\Controller;
use App\Http\Resources\HoldPayloadResource;
use App\Services\HoldStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Обслуживание бронирования слотов
 */
class HoldController extends Controller
{
    /**
     * @param SlotHolderServiceInterface $slotService - сервисный слой (кэш, транзакции, идемпотентность)
     * @param HoldStateServiceInterface $stateService - меняем состояние холда (StateMachine) {@see HoldStateService}
     */
    public function __construct(
        private readonly SlotHolderServiceInterface $slotService,
        private readonly HoldStateServiceInterface  $stateService,
    ) {}

    /**
     * Создание холда в слоте
     *
     * Метод: POST /slots/{id}/hold
     *
     * Создает запись в таблице holds со статусом held.
     * Проверяет доступность мест и возвращает 409 Conflict, если capacity исчерпан.
     * Повторный запрос с тем же ключом возвращает прежний результат (идемпотентность).
     * Холды живут 5 минут (фоновую очистку можно не реализовывать).
     * @throws SlotsExceptions
     */
    public function create(Request $request, int $id): JsonResponse
    {
        $idempotencyKey = (string) $request->attributes->get('idempotency_key');
        $payload = $this->slotService->create($id, $idempotencyKey);

        return HoldPayloadResource::make($payload)->response();
    }

    /**
     * Подтверждение холда в слоте.
     *
     * Метод: POST /holds/{id}/confirm
     *
     * Переводит холд в состояние confirmed.
     * Атомарно уменьшает remaining в слоте на 1 с защитой от оверсела.
     * При отсутствии мест возвращает 409 Conflict.
     * После успешного подтверждения инвалидирует кеш доступности.
     */
    public function confirm(int $id): bool
    {
        return $this->stateService->confirm($id);
    }

    /**
     * Отмена холда в слоте
     *
     * Метод: DELETE /holds/{id}
     *
     * Меняет состояние холда на cancelled.
     * Возвращает слот в доступ, обновляя остаток.
     * Инвалидирует кеш доступных слотов.
     */
    public function cancel(int $id): bool
    {
        return $this->stateService->cancel($id);
    }
}
