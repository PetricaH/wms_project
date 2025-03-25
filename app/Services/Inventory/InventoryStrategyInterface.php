<?php

namespace App\Service\Inventory;

use App\Models\BinLocation;
use App\Models\Product;

interface InventoryStrategyInterface {
    // receive inventory
    public function receive(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array;

    // transfer inventory from one location to another
    public function transfer(
        Product $product,
        BinLocation $fromLocation,
        BinLocation $toLocation,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array;

    public function pick(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array;

    // adjust inventory quantity
    public function adjust(
        Product $product,
        BinLocation $location,
        float $newQuantity,
        array $attributes = [],
        ?string $reason = null
    ): array;

    // reserve inventory
    public function reserve(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array;

    // unreserve inventory
    public function unreserve(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array;
}