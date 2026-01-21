<?php

namespace Tests\Unit;

use App\Contracts\HoldCreateRepositoryInterface;
use App\Contracts\SlotCapacityRepositoryInterface;
use App\Enums\HoldStatus;
use App\Exceptions\SlotsExceptions;
use App\Models\Hold;
use App\Services\SlotHolderService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class SlotHolderServiceTest extends TestCase
{
    /**
     * Возвращает hold из кеша и не обращается к репозиториям.
     */
    public function test_create_returns_cached_hold(): void
    {
        $slotId = 10;
        $idempotencyKey = '11111111-1111-1111-1111-111111111111';
        $cacheKey = "idempotency:hold:{$idempotencyKey}";

        $hold = (new Hold())->forceFill([
            'id' => 7,
            'slots_id' => $slotId,
            'status' => HoldStatus::Held,
        ]);

        Cache::shouldReceive('get')
            ->once()
            ->with($cacheKey)
            ->andReturn($hold);
        Cache::shouldReceive('put')->never();

        $slotRepository = Mockery::mock(SlotCapacityRepositoryInterface::class);
        $slotRepository->shouldNotReceive('get');

        $holdRepository = Mockery::mock(HoldCreateRepositoryInterface::class);
        $holdRepository->shouldNotReceive('create');

        $service = new SlotHolderService($slotRepository, $holdRepository);

        $result = $service->create($slotId, $idempotencyKey);

        $this->assertSame($hold, $result);
    }

    /**
     * Создает hold для слота и кеширует результат.
     */
    public function test_create_creates_hold_and_caches_it(): void
    {
        $slotId = 20;
        $idempotencyKey = '22222222-2222-2222-2222-222222222222';
        $cacheKey = "idempotency:hold:{$idempotencyKey}";

        Cache::shouldReceive('get')
            ->once()
            ->with($cacheKey)
            ->andReturn(null);
        Cache::shouldReceive('put')
            ->once()
            ->with(
                $cacheKey,
                Mockery::type(Hold::class),
                Mockery::type(Carbon::class)
            );

        $slotRepository = Mockery::mock(SlotCapacityRepositoryInterface::class);
        $slotRepository->shouldReceive('get')
            ->once()
            ->with($slotId)
            ->andReturn([
                'id' => $slotId,
                'capacity' => 2,
                'active_holds' => 1,
            ]);

        $hold = (new Hold())->forceFill([
            'id' => 11,
            'slots_id' => $slotId,
            'status' => HoldStatus::Held,
        ]);

        $holdRepository = Mockery::mock(HoldCreateRepositoryInterface::class);
        $holdRepository->shouldReceive('create')
            ->once()
            ->with($slotId)
            ->andReturn($hold);

        $service = new SlotHolderService($slotRepository, $holdRepository);

        $result = $service->create($slotId, $idempotencyKey);

        $this->assertSame($hold, $result);
    }

    /**
     * Бросает исключение, если слот не найден.
     */
    public function test_create_throws_when_slot_not_found(): void
    {
        $slotId = 30;
        $idempotencyKey = '33333333-3333-3333-3333-333333333333';
        $cacheKey = "idempotency:hold:{$idempotencyKey}";

        Cache::shouldReceive('get')
            ->once()
            ->with($cacheKey)
            ->andReturn(null);
        Cache::shouldReceive('put')->never();

        $slotRepository = Mockery::mock(SlotCapacityRepositoryInterface::class);
        $slotRepository->shouldReceive('get')
            ->once()
            ->with($slotId)
            ->andReturn(null);

        $holdRepository = Mockery::mock(HoldCreateRepositoryInterface::class);
        $holdRepository->shouldNotReceive('create');

        $service = new SlotHolderService($slotRepository, $holdRepository);

        $this->expectException(SlotsExceptions::class);
        $this->expectExceptionMessage("Slot '{$slotId}' not found");
        $this->expectExceptionCode(404);

        $service->create($slotId, $idempotencyKey);
    }

    /**
     * Бросает конфликт при переполнении слота.
     */
    public function test_create_throws_conflict_when_slot_is_full(): void
    {
        $slotId = 40;
        $idempotencyKey = '44444444-4444-4444-4444-444444444444';
        $cacheKey = "idempotency:hold:{$idempotencyKey}";

        Cache::shouldReceive('get')
            ->once()
            ->with($cacheKey)
            ->andReturn(null);
        Cache::shouldReceive('put')->never();

        $slotRepository = Mockery::mock(SlotCapacityRepositoryInterface::class);
        $slotRepository->shouldReceive('get')
            ->once()
            ->with($slotId)
            ->andReturn([
                'id' => $slotId,
                'capacity' => 1,
                'active_holds' => 1,
            ]);

        $holdRepository = Mockery::mock(HoldCreateRepositoryInterface::class);
        $holdRepository->shouldNotReceive('create');

        $service = new SlotHolderService($slotRepository, $holdRepository);

        $this->expectException(SlotsExceptions::class);
        $this->expectExceptionMessage('Slots conflict');
        $this->expectExceptionCode(409);

        $service->create($slotId, $idempotencyKey);
    }
}
