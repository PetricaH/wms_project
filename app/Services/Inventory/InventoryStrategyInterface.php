<?php

namespace App\Services\Inventory;

use App\Models\BinLocation;
use App\Models\Product;

interface InventoryStrategyInterface
{
    /**
     * Receive inventory.
     * 
     * @param Product $product
     * @param BinLocation $location
     * @param float $quantity
     * @param array $attributes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return array
     */
    public function receive(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array;

    /**
     * Transfer inventory from one location to another.
     * 
     * @param Product $product
     * @param BinLocation $fromLocation
     * @param BinLocation $toLocation
     * @param float $quantity
     * @param array $attributes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return array
     */
    public function transfer(
        Product $product,
        BinLocation $fromLocation,
        BinLocation $toLocation,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array;

    /**
     * Pick inventory from a location.
     * 
     * @param Product $product
     * @param BinLocation $location
     * @param float $quantity
     * @param array $attributes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return array
     */
    public function pick(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array;

    /**
     * Adjust inventory quantity.
     * 
     * @param Product $product
     * @param BinLocation $location
     * @param float $newQuantity
     * @param array $attributes
     * @param string|null $reason
     * @return array
     */
    public function adjust(
        Product $product,
        BinLocation $location,
        float $newQuantity,
        array $attributes = [],
        ?string $reason = null
    ): array;

    /**
     * Reserve inventory.
     * 
     * @param Product $product
     * @param BinLocation $location
     * @param float $quantity
     * @param array $attributes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return array
     */
    public function reserve(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array;

    /**
     * Unreserve inventory.
     * 
     * @param Product $product
     * @param BinLocation $location
     * @param float $quantity
     * @param array $attributes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return array
     */
    public function unreserve(
        Product $product,
        BinLocation $location,
        float $quantity,
        array $attributes = [],
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array;
}