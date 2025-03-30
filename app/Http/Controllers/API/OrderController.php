<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @var OrderService Service for handling order operations
     */
    protected $orderService;

    /**
     * Constructor injects the OrderService dependency
     *
     * @param OrderService $orderService Service for order management
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Get a list of orders with optional filtering
     * 
     * @param Request $request HTTP request with query parameters
     * @return JsonResponse JSON response with orders data
     */
    public function index(Request $request): JsonResponse
    {
        // Get query parameters for filtering
        $status = $request->query('status');
        $paymentStatus = $request->query('payment_status');
        $warehouseId = $request->query('warehouse_id');
        $assignedTo = $request->query('assigned_to');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $search = $request->query('search');
        
        // Start building the query
        $query = Order::with(['items.product', 'warehouse', 'assignedUser']);
        
        // Apply filters if provided
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        
        if ($assignedTo) {
            $query->where('assigned_to', $assignedTo);
        }
        
        if ($fromDate) {
            $query->where('order_date', '>=', $fromDate);
        }
        
        if ($toDate) {
            $query->where('order_date', '<=', $toDate);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('external_order_id', 'like', "%{$search}%");
            });
        }
        
        // Get overdue orders if requested
        if ($request->query('overdue') === 'true') {
            $query->overdue();
        }
        
        // Get orders due soon if requested
        if ($daysParameter = $request->query('due_within_days')) {
            $days = (int) $daysParameter;
            $query->dueWithinDays($days);
        }
        
        // Paginate the results
        $perPage = $request->query('per_page', 15);
        $orders = $query->orderBy('order_date', 'desc')->paginate($perPage);
        
        // Return the results as JSON
        return response()->json($orders);
    }

    /**
     * Create a new order
     * 
     * @param CreateOrderRequest $request Validated request for creating an order
     * @return JsonResponse JSON response with the created order
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            // Create the order using the order service
            $order = $this->orderService->createOrder(
                $request->input('order'),
                $request->input('items')
            );
            
            // Return a success response with the created order
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['items', 'warehouse']),
            ], 201);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get details of a specific order
     * 
     * @param int $id Order ID
     * @return JsonResponse JSON response with the order details
     */
    public function show(int $id): JsonResponse
    {
        // Load the order with needed relationships
        $order = Order::with([
            'items.product', 
            'warehouse', 
            'assignedUser', 
            'pickedByUser', 
            'packedByUser'
        ])->findOrFail($id);
        
        // Return the order data
        return response()->json($order);
    }

    /**
     * Update an existing order
     * 
     * @param UpdateOrderRequest $request Validated request for updating an order
     * @param int $id Order ID
     * @return JsonResponse JSON response with the updated order
     */
    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        try {
            // Find the order
            $order = Order::findOrFail($id);
            
            // Update order attributes from the request
            $order->fill($request->input('order'));
            $order->save();
            
            // Return a success response with the updated order
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order->load(['items', 'warehouse']),
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error updating order: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process payment for an order
     * 
     * @param ProcessPaymentRequest $request Validated request for processing payment
     * @param int $id Order ID
     * @return JsonResponse JSON response with payment result
     */
    public function processPayment(ProcessPaymentRequest $request, int $id): JsonResponse
    {
        try {
            // Process the payment using the order service
            $result = $this->orderService->processPayment(
                $id,
                $request->input('payment')
            );
            
            // Return a success response with the payment result
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Allocate inventory for an order
     * 
     * @param int $id Order ID
     * @return JsonResponse JSON response with allocation result
     */
    public function allocateInventory(int $id): JsonResponse
    {
        try {
            // Allocate inventory using the order service
            $result = $this->orderService->allocateOrderInventory($id);
            
            // Return a success response with the allocation result
            return response()->json([
                'success' => true,
                'message' => 'Inventory allocated successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error allocating inventory: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel an order
     * 
     * @param Request $request HTTP request with cancellation reason
     * @param int $id Order ID
     * @return JsonResponse JSON response with cancellation result
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        // Validate the request
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        try {
            // Cancel the order using the order service
            $order = $this->orderService->cancelOrder(
                $id,
                $request->input('reason')
            );
            
            // Return a success response with the cancelled order
            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling order: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Assign an order to a user
     * 
     * @param Request $request HTTP request with user ID
     * @param int $id Order ID
     * @return JsonResponse JSON response with assignment result
     */
    public function assignToUser(Request $request, int $id): JsonResponse
    {
        // Validate the request
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        
        try {
            // Assign the order using the order service
            $order = $this->orderService->assignOrderToUser(
                $id,
                $request->input('user_id')
            );
            
            // Return a success response with the assigned order
            return response()->json([
                'success' => true,
                'message' => 'Order assigned successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error assigning order: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Generate a pick list for an order
     * 
     * @param int $id Order ID
     * @return JsonResponse JSON response with pick list data
     */
    public function generatePickList(int $id): JsonResponse
    {
        try {
            // Generate the pick list using the order service
            $pickList = $this->orderService->generatePickList($id);
            
            // Return a success response with the pick list
            return response()->json([
                'success' => true,
                'data' => $pickList,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error generating pick list: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get orders that are ready for picking
     * 
     * @param Request $request HTTP request with optional warehouse filter
     * @return JsonResponse JSON response with orders data
     */
    public function getOrdersReadyForPicking(Request $request): JsonResponse
    {
        try {
            // Get warehouse ID from query parameter if provided
            $warehouseId = $request->query('warehouse_id');
            
            // Get ready orders using the order service
            $orders = $this->orderService->getOrdersReadyForPicking($warehouseId);
            
            // Return a success response with the orders
            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving orders: ' . $e->getMessage(),
            ], 400);
        }
    }
}