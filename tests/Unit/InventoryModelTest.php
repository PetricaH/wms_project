<?php

namespace Tests\Unit;

use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryModelTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;
    protected Warehouse $warehouse;
    protected Zone $zone;
    protected BinLocation $location;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->product = Product::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->zone = Zone::factory()->create(['warehouse_id' => $this->warehouse->id]);
        $this->location = BinLocation::factory()->create(['zone_id' => $this->zone->id]);
    }

    /** @test */
    public function it_automatically_calculates_available_quantity()
    {
        $inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'quantity' => 100,
            'reserved_quantity' => 30,
            // Note: available_quantity is calculated automatically
        ]);

        $this->assertEquals(70, $inventory->available_quantity);

        // Test that it recalculates on update
        $inventory->quantity = 150;
        $inventory->save();

        $this->assertEquals(120, $inventory->available_quantity);

        $inventory->reserved_quantity = 50;
        $inventory->save();

        $this->assertEquals(100, $inventory->available_quantity);
    }

    /** @test */
    public function it_can_determine_if_inventory_is_expired()
    {
        // Create non-expired inventory
        $futureInventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'expiry_date' => now()->addDays(30),
        ]);

        // Create expired inventory
        $expiredInventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'expiry_date' => now()->subDays(1),
        ]);

        // Create inventory with no expiry date
        $noExpiryInventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'expiry_date' => null,
        ]);

        $this->assertFalse($futureInventory->isExpired());
        $this->assertTrue($expiredInventory->isExpired());
        $this->assertFalse($noExpiryInventory->isExpired());
    }

    /** @test */
    public function it_calculates_days_until_expiry()
    {
        // Create inventory with expiry 10 days from now
        $inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'expiry_date' => now()->addDays(10),
        ]);

        // Exact day might be off by 1 due to time differences in test environment
        $this->assertEqualsWithDelta(10, $inventory->days_until_expiry, 1);

        // Test with null expiry date
        $noExpiryInventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'expiry_date' => null,
        ]);

        $this->assertNull($noExpiryInventory->days_until_expiry);

        // Test with past expiry date
        $expiredInventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'expiry_date' => now()->subDays(5),
        ]);

        $this->assertEqualsWithDelta(-5, $expiredInventory->days_until_expiry, 1);
    }

    /** @test */
    public function it_can_filter_by_scopes()
    {
        // Create some inventory records with different conditions
        $inStockInventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'quantity' => 100,
            'reserved_quantity' => 30,
        ]);
        
        $zeroStockInventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'quantity' => 0,
            'reserved_quantity' => 0,
        ]);
        
        $fullyReservedInventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'quantity' => 50,
            'reserved_quantity' => 50,
        ]);
        
        $expiredInventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'quantity' => 20,
            'reserved_quantity' => 0,
            'expiry_date' => now()->subDays(1),
        ]);
        
        $futureExpiryInventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'quantity' => 30,
            'reserved_quantity' => 10,
            'expiry_date' => now()->addDays(30),
        ]);

        // Test inStock scope
        $this->assertEquals(4, Inventory::inStock()->count());
        
        // Test available scope
        $this->assertEquals(3, Inventory::available()->count());
        
        // Test expiresBefore scope
        $this->assertEquals(2, Inventory::expiresBefore(now()->addDays(10))->count());
        
        // Test notExpired scope
        $this->assertEquals(4, Inventory::notExpired()->count());
        
        // Test combining scopes
        $this->assertEquals(2, Inventory::inStock()->available()->notExpired()->count());
    }
}