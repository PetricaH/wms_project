<?php

namespace App\Providers;

use App\Services\Inventory\FifoInventoryStrategy;
use App\Services\Inventory\InventoryStrategyInterface;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(InventoryStrategyInterface::class, FifoInventoryStrategy::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}