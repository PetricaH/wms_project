<?php

namespace Tests\Unit;

use App\Models\BinLocation;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryMovementTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;
    protected BinLocation $sourceLocation;
    protected BinLocation $destLocation;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $zone = Zone::factory()->create(['warehouse_id' => $warehouse->id]);
        $this->sourceLocation = BinLocation::factory()->create(['zone_id' => $zone->id]);
        $this->destLocation = BinLocation::factory()->create(['zone_id' => $zone->id]);
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_creates_movement_records_correctly()
    {
        $movement = InventoryMovement::factory()->create([
            'product_id' => $this->product->id,
            'from_location_id' => $this->sourceLocation->id,
            'to_location_id' => $this->destLocation->id,
            'quantity' => 50,
            'unit_of_measure' => 'EA',
            'lot_number' => 'LOT123',
            'batch_number' => 'BATCH001',
            'movement_type' => InventoryMovement::TYPE_TRANSFER,
            'reason' => 'Test transfer',
            'performed_by' => $this->user->id
        ]);

        $this->assertEquals($this->product->id, $movement->product_id);
        $this->assertEquals($this->sourceLocation->id, $movement->from_location_id);
        $this->assertEquals($this->destLocation->id, $movement->to_location_id);
        $this->assertEquals(50, $movement->quantity);
        $this->assertEquals('EA', $movement->unit_of_measure);
        $this->assertEquals('LOT123', $movement->lot_number);
        $this->assertEquals('BATCH001', $movement->batch_number);
        $this->assertEquals(InventoryMovement::TYPE_TRANSFER, $movement->movement_type);
        $this->assertEquals('Test transfer', $movement->reason);
        $this->assertEquals($this->user->id, $movement->performed_by);
    }

    /** @test */
    public function it_can_store_fifo_layers()
    {
        $fifoLayers = [
            [
                'inventory_id' => 1,
                'lot_number' => 'LOT123',
                'batch_number' => 'BATCH001',
                'quantity' => 30,
                'received_date' => '2023-01-01'
            ],
            [
                'inventory_id' => 2,
                'lot_number' => 'LOT124',
                'batch_number' => 'BATCH002',
                'quantity' => 20,
                'received_date' => '2023-01-15'
            ]
        ];

        $movement = InventoryMovement::factory()->create([
            'product_id' => $this->product->id,
            'from_location_id' => $this->sourceLocation->id,
            'movement_type' => InventoryMovement::TYPE_PICK,
            'quantity' => 50,
            'fifo_layers' => $fifoLayers
        ]);

        $this->assertEquals($fifoLayers, $movement->fifo_layers);
        $this->assertEquals(2, count($movement->fifo_layers));
        $this->assertEquals(30, $movement->fifo_layers[0]['quantity']);
        $this->assertEquals('LOT124', $movement->fifo_layers[1]['lot_number']);
    }

    /** @test */
    public function it_can_filter_by_scopes()
    {
        // Create movements of different types
        $receiveMovement = InventoryMovement::factory()->create([
            'product_id' => $this->product->id,
            'to_location_id' => $this->destLocation->id,
            'movement_type' => InventoryMovement::TYPE_RECEIVE,
            'created_at' => Carbon::yesterday()
        ]);
        
        $transferMovement = InventoryMovement::factory()->create([
            'product_id' => $this->product->id,
            'from_location_id' => $this->sourceLocation->id,
            'to_location_id' => $this->destLocation->id,
            'movement_type' => InventoryMovement::TYPE_TRANSFER,
            'created_at' => Carbon::today()
        ]);
        
        $pickMovement = InventoryMovement::factory()->create([
            'product_id' => $this->product->id,
            'from_location_id' => $this->sourceLocation->id,
            'movement_type' => InventoryMovement::TYPE_PICK,
            'created_at' => Carbon::tomorrow()
        ]);

        // Test type scope
        $this->assertEquals(1, InventoryMovement::ofType(InventoryMovement::TYPE_RECEIVE)->count());
        $this->assertEquals(1, InventoryMovement::ofType(InventoryMovement::TYPE_TRANSFER)->count());
        $this->assertEquals(1, InventoryMovement::ofType(InventoryMovement::TYPE_PICK)->count());

        // Test date filter scope
        $this->assertEquals(2, InventoryMovement::betweenDates(Carbon::yesterday(), Carbon::today())->count());
        $this->assertEquals(3, InventoryMovement::betweenDates(Carbon::yesterday(), Carbon::tomorrow())->count());
        $this->assertEquals(1, InventoryMovement::betweenDates(Carbon::today(), Carbon::today())->count());
    }

    /** @test */
    public function it_can_filter_by_reference()
    {
        // Create movements with different references
        InventoryMovement::factory()->create([
            'product_id' => $this->product->id,
            'movement_type' => InventoryMovement::TYPE_RECEIVE,
            'reference_type' => 'order',
            'reference_id' => 123
        ]);
        
        InventoryMovement::factory()->create([
            'product_id' => $this->product->id,
            'movement_type' => InventoryMovement::TYPE_PICK,
            'reference_type' => 'order',
            'reference_id' => 123
        ]);
        
        InventoryMovement::factory()->create([
            'product_id' => $this->product->id,
            'movement_type' => InventoryMovement::TYPE_PICK,
            'reference_type' => 'order',
            'reference_id' => 456
        ]);
        
        InventoryMovement::factory()->create([
            'product_id' => $this->product->id,
            'movement_type' => InventoryMovement::TYPE_TRANSFER,
            'reference_type' => 'transfer',
            'reference_id' => 789
        ]);

        // Test reference scope
        $this->assertEquals(2, InventoryMovement::forReference('order', 123)->count());
        $this->assertEquals(1, InventoryMovement::forReference('order', 456)->count());
        $this->assertEquals(1, InventoryMovement::forReference('transfer', 789)->count());
        $this->assertEquals(0, InventoryMovement::forReference('order', 999)->count());
    }
}