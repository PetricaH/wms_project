<?php

namespace Tests\Unit;

use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Zone;
use App\Services\Inventory\FifoInventoryStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FifoInventoryStrategyTest extends TestCase
{
    use RefreshDatabase;

    protected FifoInventoryStrategy $fifoStrategy;
    protected Product $product;
    protected Warehouse $warehouse;
    protected Zone $zone;
    protected BinLocation $location1;
    protected BinLocation $location2;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create test data
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-001',
            'cost' => 10.00,
        ]);

        $this->warehouse = Warehouse::factory()->create([
            'name' => 'Test Warehouse',
            'code' => 'TW1',
        ]);

        $this->zone = Zone::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'name' => 'Test Zone',
            'code' => 'TZ1',
        ]);

        $this->location1 = BinLocation::factory()->create([
            'zone_id' => $this->zone->id,
            'name' => 'Test Location 1',
            'code' => 'A01',
        ]);

        $this->location2 = BinLocation::factory()->create([
            'zone_id' => $this->zone->id,
            'name' => 'Test Location 2',
            'code' => 'A02',
        ]);

        $this->fifoStrategy = new FifoInventoryStrategy();
    }

    /** @test */
    public function it_can_receive_inventory()
    {
        // Execute receive operation
        $result = $this->fifoStrategy->receive(
            $this->product,
            $this->location1,
            100,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
                'unit_cost' => 15.00,
            ]
        );

        // Check the movement record
        $this->assertNotNull($result['movement']);
        $this->assertEquals(InventoryMovement::TYPE_RECEIVE, $result['movement']->movement_type);
        $this->assertEquals(100, $result['movement']->quantity);
        $this->assertEquals('LOT123', $result['movement']->lot_number);
        $this->assertEquals('BATCH001', $result['movement']->batch_number);

        // Check the inventory record
        $this->assertNotNull($result['inventory']);
        $this->assertEquals($this->product->id, $result['inventory']->product_id);
        $this->assertEquals($this->location1->id, $result['inventory']->location_id);
        $this->assertEquals(100, $result['inventory']->quantity);
        $this->assertEquals(0, $result['inventory']->reserved_quantity);
        $this->assertEquals(100, $result['inventory']->available_quantity);
        $this->assertEquals('LOT123', $result['inventory']->lot_number);
        $this->assertEquals('BATCH001', $result['inventory']->batch_number);

        // Check the transaction record
        $this->assertNotNull($result['transaction']);
        $this->assertEquals(InventoryTransaction::TYPE_INCREMENT, $result['transaction']->transaction_type);
        $this->assertEquals(100, $result['transaction']->quantity);
        $this->assertEquals(15.00, $result['transaction']->unit_cost);
        $this->assertEquals(1500.00, $result['transaction']->total_cost);
    }

    /** @test */
    public function it_can_transfer_inventory()
    {
        // First, receive inventory
        $this->fifoStrategy->receive(
            $this->product,
            $this->location1,
            100,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
            ]
        );

        // Execute transfer operation
        $result = $this->fifoStrategy->transfer(
            $this->product,
            $this->location1,
            $this->location2,
            40,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
            ]
        );

        // Check the movement record
        $this->assertNotNull($result['movement']);
        $this->assertEquals(InventoryMovement::TYPE_TRANSFER, $result['movement']->movement_type);
        $this->assertEquals(40, $result['movement']->quantity);
        $this->assertEquals($this->location1->id, $result['movement']->from_location_id);
        $this->assertEquals($this->location2->id, $result['movement']->to_location_id);

        // Check the source inventory
        $this->assertNotNull($result['source_inventory']);
        $this->assertEquals(60, $result['source_inventory']->quantity);
        $this->assertEquals(60, $result['source_inventory']->available_quantity);

        // Check the destination inventory
        $this->assertNotNull($result['destination_inventory']);
        $this->assertEquals(40, $result['destination_inventory']->quantity);
        $this->assertEquals(40, $result['destination_inventory']->available_quantity);

        // Check there are two transactions
        $this->assertCount(2, $result['transactions']);
    }

    /** @test */
    public function it_can_pick_inventory_using_fifo()
    {
        // Receive inventory in two batches with different dates
        $this->fifoStrategy->receive(
            $this->product,
            $this->location1,
            50,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
                'received_date' => '2023-01-01',
            ]
        );

        $this->fifoStrategy->receive(
            $this->product,
            $this->location1,
            50,
            [
                'lot_number' => 'LOT124',
                'batch_number' => 'BATCH002',
                'received_date' => '2023-01-10',
            ]
        );

        // Execute pick operation that will need to use both batches
        $result = $this->fifoStrategy->pick(
            $this->product,
            $this->location1,
            70
        );

        // Check the movement record
        $this->assertNotNull($result['movement']);
        $this->assertEquals(InventoryMovement::TYPE_PICK, $result['movement']->movement_type);
        $this->assertEquals(70, $result['movement']->quantity);

        // Check that FIFO was applied (oldest batch first)
        $fifoLayers = $result['fifo_layers'];
        $this->assertCount(2, $fifoLayers);
        $this->assertEquals(50, $fifoLayers[0]['quantity']); // First batch completely consumed
        $this->assertEquals(20, $fifoLayers[1]['quantity']); // Second batch partially consumed

        // Check inventory levels
        $firstBatchInventory = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->location1->id)
            ->where('lot_number', 'LOT123')
            ->first();

        $secondBatchInventory = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->location1->id)
            ->where('lot_number', 'LOT124')
            ->first();

        $this->assertEquals(0, $firstBatchInventory->quantity);
        $this->assertEquals(30, $secondBatchInventory->quantity);
    }

    /** @test */
    public function it_can_adjust_inventory()
    {
        // First, receive inventory
        $this->fifoStrategy->receive(
            $this->product,
            $this->location1,
            100,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
            ]
        );

        // Execute adjust operation to decrease quantity
        $result = $this->fifoStrategy->adjust(
            $this->product,
            $this->location1,
            70,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
            ],
            'Stock count adjustment'
        );

        // Check the movement record
        $this->assertNotNull($result['movement']);
        $this->assertEquals('adjust', $result['movement']->movement_type);
        $this->assertEquals(30, $result['movement']->quantity); // |100 - 70| = 30
        $this->assertEquals('Stock count adjustment', $result['movement']->reason);

        // Check the inventory record
        $this->assertNotNull($result['inventory']);
        $this->assertEquals(70, $result['inventory']->quantity);
        $this->assertEquals(70, $result['inventory']->available_quantity);

        // Now adjust to increase
        $result = $this->fifoStrategy->adjust(
            $this->product,
            $this->location1,
            120,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
            ],
            'Stock count adjustment up'
        );

        // Check the inventory record after increase
        $this->assertEquals(120, $result['inventory']->quantity);
        $this->assertEquals(120, $result['inventory']->available_quantity);
    }

    /** @test */
    public function it_can_reserve_and_unreserve_inventory()
    {
        // First, receive inventory
        $this->fifoStrategy->receive(
            $this->product,
            $this->location1,
            100,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
            ]
        );

        // Execute reserve operation
        $result = $this->fifoStrategy->reserve(
            $this->product,
            $this->location1,
            30,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
            ],
            'order',
            123
        );

        // Check inventory after reservation
        $inventory = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->location1->id)
            ->first();

        $this->assertEquals(100, $inventory->quantity);
        $this->assertEquals(30, $inventory->reserved_quantity);
        $this->assertEquals(70, $inventory->available_quantity);

        // Execute unreserve operation
        $result = $this->fifoStrategy->unreserve(
            $this->product,
            $this->location1,
            15,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
            ],
            'order',
            123
        );

        // Check inventory after unreservation
        $inventory->refresh();
        $this->assertEquals(100, $inventory->quantity);
        $this->assertEquals(15, $inventory->reserved_quantity);
        $this->assertEquals(85, $inventory->available_quantity);
    }

    /** @test */
    public function it_enforces_availability_constraints()
    {
        // First, receive a small quantity
        $this->fifoStrategy->receive(
            $this->product,
            $this->location1,
            50,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
            ]
        );

        // Reserve part of it
        $this->fifoStrategy->reserve(
            $this->product,
            $this->location1,
            20,
            [
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
            ]
        );

        // Attempt to pick more than available
        try {
            $this->fifoStrategy->pick(
                $this->product,
                $this->location1,
                40
            );
            $this->fail('Exception should have been thrown when picking more than available');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Insufficient available inventory', $e->getMessage());
        }

        // Attempt to transfer more than available
        try {
            $this->fifoStrategy->transfer(
                $this->product,
                $this->location1,
                $this->location2,
                40
            );
            $this->fail('Exception should have been thrown when transferring more than available');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Insufficient available inventory', $e->getMessage());
        }

        // Attempt to reserve more than available
        try {
            $this->fifoStrategy->reserve(
                $this->product,
                $this->location1,
                40
            );
            $this->fail('Exception should have been thrown when reserving more than available');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Insufficient available inventory', $e->getMessage());
        }
    }
}