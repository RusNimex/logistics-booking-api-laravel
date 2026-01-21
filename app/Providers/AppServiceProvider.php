<?php

namespace App\Providers;

use App\Contracts\SlotServiceCollectorInterface;
use App\Contracts\SlotRepositoryInterface;
use App\Contracts\SlotHolderServiceInterface;
use App\Contracts\HoldActiveRepositoryInterface;
use App\Contracts\HoldCreateRepositoryInterface;
use App\Contracts\SlotCapacityRepositoryInterface;
use App\Repository\CachedSlotRepository;
use App\Repository\DbSlotRepository;
use App\Repository\HoldActiveRepository;
use App\Repository\HoldCreateRepository;
use App\Repository\SlotCapacityRepository;
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

        // Сервис слотов
        $this->app->bind(SlotServiceCollectorInterface::class, SlotServiceCollector::class);

        // Репозитории для холдов
        $this->app->bind(SlotCapacityRepositoryInterface::class, SlotCapacityRepository::class);
        $this->app->bind(HoldCreateRepositoryInterface::class, HoldCreateRepository::class);

        // Сервис создания холдов
        $this->app->bind(SlotHolderServiceInterface::class, SlotHolderService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
