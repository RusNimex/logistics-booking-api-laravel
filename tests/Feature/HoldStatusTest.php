<?php

namespace Tests\Feature;

use App\Enums\HoldStatus;
use App\Models\Hold;
use App\Repository\HoldCreateRepository;
use App\Repository\HoldStateRepository;
use App\Repository\SlotCapacityRepository;
use App\Services\SlotHolderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HoldStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Создает hold со статусом held и корректным TTL.
     */
    public function test_hold_creation_sets_status_and_expires_at(): void
    {
        Carbon::setTestNow('2026-01-22 10:00:00');

        $slotId = DB::table('slots')->insertGetId([
            'capacity' => 2,
            'remaining' => 2,
        ]);

        $slotCapacityRepository = new SlotCapacityRepository();
        $holdCreateRepository = new HoldCreateRepository($slotCapacityRepository);

        $hold = $holdCreateRepository->create($slotId);

        $this->assertSame($slotId, $hold->slots_id);
        $this->assertSame(HoldStatus::Held, $hold->status);
        $this->assertNotNull($hold->expires_at);
        $this->assertTrue(
            $hold->expires_at->equalTo(
                Carbon::now()->addMinutes(SlotHolderService::HOLD_TTL_MINUTES)
            )
        );

        Carbon::setTestNow();
    }

    /**
     * Уменьшает remaining слота при подтверждении hold.
     */
    public function test_confirm_decrements_slot_remaining(): void
    {
        $slotId = DB::table('slots')->insertGetId([
            'capacity' => 2,
            'remaining' => 2,
        ]);

        $holdId = DB::table('holds')->insertGetId([
            'slots_id' => $slotId,
            'status' => HoldStatus::Held->value,
            'expires_at' => Carbon::now()->addMinute(),
        ]);

        $repository = new HoldStateRepository();

        $this->assertTrue($repository->confirm($holdId));

        $remaining = DB::table('slots')->where('id', $slotId)->value('remaining');
        $holdStatus = Hold::query()->findOrFail($holdId)->status;

        $this->assertSame(1, $remaining);
        $this->assertSame(HoldStatus::Confirmed, $holdStatus);
    }

    /**
     * Увеличивает remaining слота при отмене hold.
     */
    public function test_cancel_increments_slot_remaining(): void
    {
        $slotId = DB::table('slots')->insertGetId([
            'capacity' => 2,
            'remaining' => 1,
        ]);

        $holdId = DB::table('holds')->insertGetId([
            'slots_id' => $slotId,
            'status' => HoldStatus::Held->value,
            'expires_at' => Carbon::now()->addMinute(),
        ]);

        $repository = new HoldStateRepository();

        $this->assertTrue($repository->cancel($holdId));

        $remaining = DB::table('slots')->where('id', $slotId)->value('remaining');
        $holdStatus = Hold::query()->findOrFail($holdId)->status;

        $this->assertSame(2, $remaining);
        $this->assertSame(HoldStatus::Canceled, $holdStatus);
    }
}
