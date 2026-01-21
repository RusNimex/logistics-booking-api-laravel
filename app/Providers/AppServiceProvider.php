<?php

namespace App\Providers;

use App\Contracts\HoldStateRepositoryInterface;
use App\Contracts\HoldStateServiceInterface;
use App\Contracts\SlotServiceCollectorInterface;
use App\Contracts\SlotRepositoryInterface;
use App\Contracts\SlotHolderServiceInterface;
use App\Contracts\HoldCreateRepositoryInterface;
use App\Contracts\SlotCapacityRepositoryInterface;
use App\Repository\CachedSlotRepository;
use App\Repository\DbSlotRepository;
use App\Repository\HoldCreateRepository;
use App\Repository\HoldStateRepository;
use App\Repository\SlotCapacityRepository;
use App\Services\HoldStateService;
use App\Services\SlotHolderService;
use App\Services\SlotServiceCollector;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Репозиторий: декоратор кеша поверх БД
        $this->app->bind(SlotRepositoryInterface::class, function ($app) {
            $dbRepo = $app->make(DbSlotRepository::class);
            return new CachedSlotRepository($dbRepo);
        });

        // Классы слотов
        $this->app->bind(SlotServiceCollectorInterface::class, SlotServiceCollector::class);
        $this->app->bind(SlotHolderServiceInterface::class, SlotHolderService::class);
        $this->app->bind(SlotCapacityRepositoryInterface::class, SlotCapacityRepository::class);

        // Классы холдов
        $this->app->bind(HoldCreateRepositoryInterface::class, HoldCreateRepository::class);
        $this->app->bind(HoldStateRepositoryInterface::class, HoldStateRepository::class);
        $this->app->bind(HoldStateServiceInterface::class, HoldStateService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
