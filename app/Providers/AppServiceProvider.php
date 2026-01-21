<?php

namespace App\Providers;

use App\Contracts\SlotServiceCollectorInterface;
use App\Contracts\SlotRepositoryInterface;
use App\Repository\CachedSlotRepository;
use App\Repository\DbSlotRepository;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
