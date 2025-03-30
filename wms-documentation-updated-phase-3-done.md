# WMS Project Documentation

This document provides a comprehensive overview of the Warehouse Management System (WMS) project, including implementation status, technical details, and development roadmap.

## Project Overview

The WMS system is a multi-tenant application that allows different businesses to manage their warehouse operations with a focus on inventory management using the FIFO (First In, First Out) principle.

### Tech Stack
- **Backend:** Laravel 11
- **Frontend:** Vue.js 3, Tailwind CSS
- **Database:** MySQL
- **Authentication:** Laravel Sanctum
- **Multi-tenancy:** stancl/tenancy package
- **State Management:** Pinia
- **Routing:** Vue Router

## Project Implementation Status

### Phase 1: Project Setup and Foundation ✅
- [x] Create new Laravel project (`composer create-project laravel/laravel wms-project`)
- [x] Set up Git repository with README, .gitignore, and initial commit
- [x] Install Vue.js and configure Vite
  - [x] `npm install vue@next`
  - [x] `npm install @vitejs/plugin-vue`
  - [x] Configure vite.config.js for Vue support
- [x] Create basic folder structure for Laravel and Vue components
- [x] Install Tailwind CSS for styling
  - [x] `npm install -D tailwindcss postcss autoprefixer`
  - [x] `npx tailwindcss init -p`
  - [x] Configure Tailwind CSS
- [x] Set up multi-tenancy framework
  - [x] Install multi-tenant package (`composer require stancl/tenancy`)
  - [x] Configure central and tenant database connections
  - [x] Set up tenant middleware for request handling
- [x] Create core database migrations
  - [x] System tables (tenants, users, roles, permissions)
  - [x] Create database seeder for testing data
- [x] Implement authentication system
  - [x] Set up Laravel Sanctum for API tokens
  - [x] Create login/register screens in Vue
  - [x] Implement JWT token handling on frontend

### Phase 2: Core Inventory Models ✅
- [x] Design and create inventory database structure
  - [x] Products table migration and model
  - [x] Categories table migration and model
  - [x] Inventory table migration and model
  - [x] Inventory movements table migration and model
  - [x] Locations table migration and model
- [x] Implement warehouse structure models
  - [x] Warehouses table migration and model
  - [x] Zones table migration and model
  - [x] Bin locations table migration and model
- [x] Create inventory movement service
  - [x] Implement interfaces and base classes
  - [x] Create FIFO inventory strategy class
  - [x] Implement stock movement recording logic
  - [x] Create inventory transaction history logging
- [x] Build inventory API endpoints
  - [x] Inventory listing endpoint with filtering
  - [x] Inventory movement endpoints
  - [x] Product CRUD endpoints
  - [x] Location CRUD endpoints
- [x] Write unit tests for inventory models and services
  - [x] Test FIFO logic with various scenarios
  - [x] Test inventory movement calculations
  - [x] Test stock level tracking

### Phase 3: Receiving and Order Processing ✅
- [x] Create supplier and purchasing models
  - [x] Suppliers table migration and model
  - [x] Purchase orders table migration and model
  - [x] Purchase order items table migration and model
- [x] Build receiving workflow
  - [x] Create receiving service class
  - [x] Implement receiving API endpoints
  - [x] Develop validation logic for receiving
- [x] Implement order management
  - [x] Orders table migration and model
  - [x] Order items table migration and model
  - [x] Order status workflow logic
- [x] Create picking and packing workflow
  - [x] Picking service with FIFO implementation
  - [x] Pick list generation logic
  - [x] Packing confirmation process
- [x] Implement shipping integration framework
  - [x] Shipping service abstraction layer
  - [x] Tracking number management
  - [x] Support for multiple shipping providers
- [x] Implement robust permission system
  - [x] Enhanced permission verification in requests
  - [x] Permission-based middleware
  - [x] Role-based access control

### Phase 4: Frontend UI Development (PENDING)
- [ ] Set up Vue router and navigation
- [ ] Build dashboard views
- [ ] Create inventory management screens
- [ ] Develop receiving interface
- [ ] Build order management UI
- [ ] Create warehouse management components

### Phase 5: Business Adaptability Features (PENDING)
- [ ] Implement multi-business settings
- [ ] Create rules engine for business logic
- [ ] Build integration framework
- [ ] Implement reporting system
- [ ] Create user and role management

### Phase 6: Mobile Optimization (PENDING)
- [ ] Optimize UI for mobile devices
- [ ] Implement barcode scanning
- [ ] Create mobile receiving workflow
- [ ] Build mobile picking process

### Phase 7: System Optimization and Testing (PENDING)
- [ ] Performance optimization
- [ ] Implement comprehensive testing
- [ ] Create documentation
- [ ] Security audit and improvements

### Phase 8: Productization and Launch Preparation (PENDING)
- [ ] Build subscription management (if SaaS)
- [ ] Implement white-labeling
- [ ] Create onboarding flow
- [ ] Prepare deployment process

### Phase 9: Launch and Post-Launch (PENDING)
- [ ] Final QA testing
- [ ] Production deployment
- [ ] Implement analytics and monitoring
- [ ] Create customer support system

## Technical Implementation Details

### Multi-tenancy Implementation (stancl/tenancy)

The project uses the stancl/tenancy package to implement multi-tenancy:

#### Key Components:
- **Tenant Model**: `Stancl\Tenancy\Database\Models\Tenant`
- **Domain Model**: `Stancl\Tenancy\Database\Models\Domain`
- **Tenancy Bootstrappers**:
  - `Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper`
  - `Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper`
  - `Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper`
  - `Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper`

#### Configuration:
- Each tenant has its own database (`tenant_[domain]`)
- Tenant identification via subdomain
- Central database stores tenant information
- Tenant-specific migrations in `database/migrations/tenant` directory

#### Development Setup:
- Custom implementation to manage tenant databases in development
- `CreateDevTenantCommand` for creating development tenants with proper database setup

#### Tenant-Specific Sessions:
- Sessions table migration in `database/migrations/tenant` directory to handle tenant-specific sessions
- Session configuration using the database driver (`SESSION_DRIVER=database` in .env)

### Authentication and Authorization System

The authentication system uses Laravel Sanctum for API token management:

#### Key Components:
- **AuthController**: Handles registration, login, and token management
- **Middleware**: `auth:sanctum` for protected API routes
- **Frontend**: JWT token storage and handling in Pinia store

#### Permission System:
- **Permission Model**: Contains slug-based permissions with descriptions
- **Role Model**: Groups permissions for assignment to users
- **User-Role Relationship**: Many-to-many relationship between users and roles
- **Middleware**: Custom `permission` middleware for route-level permission checks
- **BaseFormRequest**: Abstract class that handles permission checks in form requests

### Database Structure

#### Core Tables (Phase 1)
- `tenants` - Tenant information (UUID-based identifiers)
- `domains` - Domain/subdomain routing for tenants
- `users` - User accounts with tenant association
- `roles` - User roles with tenant association
- `permissions` - System permissions (global)
- `permission_role` - Junction table for role permissions
- `role_user` - Junction table for user roles

#### Inventory Management Tables (Phase 2)

**Products and Categories**
- `categories` - Product categories with hierarchical structure (self-referencing)
  - `id` - Primary key
  - `parent_id` - Self-referencing foreign key for hierarchical categories
  - `name` - Category name
  - `description` - Category description
  - `is_active` - Active status flag

- `products` - Product information
  - `id` - Primary key
  - `category_id` - Foreign key to categories
  - `sku` - Stock Keeping Unit (unique)
  - `name` - Product name
  - `description` - Product description
  - `price` - Selling price
  - `cost` - Purchase/manufacturing cost
  - `upc` - Universal Product Code
  - `weight` - Product weight
  - `dimensions` - Product dimensions stored as JSON (length, width, height)
  - `attributes` - Additional product attributes stored as JSON
  - `is_active` - Active status flag

**Warehouse Structure**
- `warehouses` - Physical warehouse locations
  - `id` - Primary key
  - `name` - Warehouse name
  - `code` - Unique warehouse code
  - `address` - Physical address
  - `is_active` - Active status flag

- `zones` - Warehouse zones/areas
  - `id` - Primary key
  - `warehouse_id` - Foreign key to warehouses
  - `name` - Zone name
  - `code` - Unique zone code (within warehouse)
  - `description` - Zone description
  - `is_active` - Active status flag

- `bin_locations` - Specific storage locations within zones
  - `id` - Primary key
  - `zone_id` - Foreign key to zones
  - `name` - Location name
  - `code` - Unique location code (within zone)
  - `position` - Position data stored as JSON (aisle, bay, level)
  - `capacity` - Storage capacity
  - `is_active` - Active status flag

**Inventory Tracking**
- `inventory` - Current inventory levels by product/location
  - `id` - Primary key
  - `product_id` - Foreign key to products
  - `location_id` - Foreign key to bin_locations
  - `lot_number` - Lot identification
  - `batch_number` - Batch identification
  - `quantity` - Total quantity
  - `reserved_quantity` - Quantity reserved for orders
  - `available_quantity` - Available quantity (calculated as quantity - reserved_quantity)
  - `unit_of_measure` - Unit of measure code
  - `expiry_date` - Expiration date
  - `received_date` - Date received into inventory

- `inventory_movements` - Record of inventory movements
  - `id` - Primary key
  - `reference_type` & `reference_id` - Polymorphic relation to source (order, transfer, etc.)
  - `product_id` - Foreign key to products
  - `from_location_id` - Source location (foreign key to bin_locations)
  - `to_location_id` - Destination location (foreign key to bin_locations)
  - `quantity` - Movement quantity
  - `unit_of_measure` - Unit of measure
  - `lot_number` & `batch_number` - Lot/batch identification
  - `movement_type` - Type of movement (receive, transfer, pick, adjust, etc.)
  - `fifo_layers` - FIFO layer information stored as JSON
  - `reason` - Reason for movement
  - `performed_by` - User who performed the action

- `inventory_transactions` - Detailed transaction history for audit trail
  - `id` - Primary key
  - `inventory_movement_id` - Foreign key to inventory_movements
  - `transaction_type` - Type of transaction (increment, decrement, adjust, etc.)
  - `product_id` - Foreign key to products
  - `location_id` - Foreign key to bin_locations
  - `quantity` - Transaction quantity
  - `running_balance` - Running balance after transaction
  - `unit_cost` & `total_cost` - Cost information
  - `lot_number` & `batch_number` - Lot/batch identification
  - `performed_by` - User who performed the action
  - `created_at` - Transaction timestamp

#### Supplier and Purchasing Tables (Phase 3)

**Supplier Management**
- `suppliers` - Supplier information
  - `id` - Primary key
  - `name` - Company name
  - `code` - Unique supplier code/identifier
  - `contact_name` - Primary contact person
  - `email` - Contact email
  - `phone` - Contact phone number
  - `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country` - Address fields
  - `tax_id` - Tax identification number
  - `website` - Supplier website
  - `account_number` - Your account number with this supplier
  - `payment_terms` - Payment terms (e.g., Net 30)
  - `currency` - Default currency for this supplier
  - `lead_time_days` - Average lead time in days
  - `quality_rating` - Quality rating (e.g., 0-5)
  - `on_time_delivery_rate` - Percentage of on-time deliveries
  - `is_active` - Whether supplier is active
  - `notes` - Additional notes

- `purchase_orders` - Purchase orders to suppliers
  - `id` - Primary key
  - `po_number` - Unique purchase order number
  - `supplier_id` - Foreign key to suppliers
  - `warehouse_id` - Foreign key to warehouses
  - `created_by` - Foreign key to users
  - `order_date` - When the order was placed
  - `expected_delivery_date` - Expected delivery date
  - `received_date` - When the order was actually received
  - `currency` - Currency code
  - `subtotal`, `tax_amount`, `shipping_cost`, `total_amount` - Financial information
  - `status` - Order status (draft, awaiting_approval, approved, sent, confirmed, partially_received, fully_received, closed, cancelled)
  - `supplier_reference` - Supplier's reference number
  - `shipping_address` - Shipping address if different from warehouse
  - `notes` - Additional notes
  - `payment_terms` - Payment terms for this specific order
  - `allows_partial_receiving` - Whether partial receipts are allowed
  - `approved_at`, `approved_by`, `sent_at`, `sent_by`, `cancelled_at`, `cancelled_by` - Tracking fields

- `purchase_order_items` - Line items in purchase orders
  - `id` - Primary key
  - `purchase_order_id` - Foreign key to purchase orders
  - `product_id` - Foreign key to products
  - `line_number` - Line number within the PO
  - `quantity_ordered` - Quantity ordered
  - `quantity_received` - Quantity received so far
  - `quantity_rejected` - Quantity rejected during receiving
  - `unit_of_measure` - Unit of measure
  - `unit_price` - Price per unit
  - `tax_rate`, `tax_amount`, `discount_percentage`, `discount_amount`, `subtotal`, `total` - Financial information
  - `destination_location_id` - Where the items should be stored upon receipt
  - `status` - Item status (pending, partially_received, fully_received, over_received, cancelled)
  - `supplier_product_code` - Supplier's product code
  - `notes` - Additional notes specific to this item
  - `expected_delivery_date` - Expected delivery date for this item specifically
  - `last_received_date` - Date of last receipt
  - `receiving_history` - JSON log of receiving transactions

#### Order Management Tables (Phase 3)

**Customer Order Management**
- `orders` - Customer orders
  - `id` - Primary key
  - `order_number` - Unique order number
  - `external_order_id` - ID from external system (e.g., e-commerce platform)
  - `customer_name`, `customer_email`, `customer_phone` - Customer information
  - `shipping_address`, `billing_address` - Address information
  - `warehouse_id` - Which warehouse will fulfill this order
  - `order_date` - When the order was placed
  - `due_date` - When the order should be shipped by
  - `currency`, `subtotal`, `tax_amount`, `shipping_amount`, `discount_amount`, `total_amount` - Financial information
  - `status` - Order status (pending, processing, awaiting_payment, paid, ready_to_pick, picking, picked, packing, packed, awaiting_shipment, shipped, delivered, cancelled, returned, completed, on_hold)
  - `payment_status` - Payment status (pending, authorized, paid, partially_refunded, fully_refunded, failed)
  - `payment_method`, `payment_reference` - Payment information
  - `shipping_method`, `tracking_number`, `carrier` - Shipping information
  - `shipped_date`, `estimated_delivery_date`, `actual_delivery_date` - Shipping dates
  - `assigned_to`, `picked_at`, `picked_by`, `packed_at`, `packed_by`, `cancelled_at`, `cancelled_by` - Processing information
  - `customer_reference`, `customer_notes`, `internal_notes` - Notes and references
  - `source` - Source of the order (e.g., website, phone)
  - `metadata` - Additional metadata stored as JSON

- `order_items` - Line items in customer orders
  - `id` - Primary key
  - `order_id` - Foreign key to orders
  - `product_id` - Foreign key to products
  - `line_number` - Position in the order
  - `sku`, `name`, `description` - Product information at time of order
  - `quantity` - Quantity ordered
  - `unit_of_measure` - Unit of measure
  - `unit_price`, `tax_rate`, `tax_amount`, `discount_percentage`, `discount_amount`, `subtotal`, `total` - Financial information
  - `quantity_allocated`, `quantity_picked`, `quantity_shipped`, `quantity_returned` - Fulfillment information
  - `status` - Item status (pending, allocated, partially_allocated, picking, picked, packed, shipped, backordered, cancelled)
  - `lot_number`, `serial_number`, `expiry_date` - Lot tracking information
  - `allocation_details`, `picking_details`, `packing_details` - Fulfillment details stored as JSON
  - `notes` - Notes specific to this item
  - `metadata` - Additional metadata stored as JSON

### Service Layer Implementation

#### Inventory Movement Strategy (Phase 2)

The system implements a FIFO (First In, First Out) inventory strategy through a service layer:

**Inventory Strategy Interface and Base Class**
- Defines contract for inventory operations (receive, transfer, pick, adjust, reserve, unreserve)
- Implements common functionality for all inventory strategies
- Ensures consistent inventory transaction recording

**FIFO Implementation**
- Tracks inventory by received date
- Ensures oldest inventory is consumed first
- Records FIFO layers when picking or transferring
- Maintains complete transaction history
- Enforces inventory availability constraints

#### Receiving Workflow (Phase 3)

The receiving process is implemented through the ReceivingService:

**Key Features**
- **Purchase Order Receipt**: Process items received against purchase orders
- **Quality Inspection**: Record quality inspection results for received items
- **Rejection Handling**: Process and track rejected items with reasons
- **FIFO Integration**: Update inventory records using FIFO strategy
- **Supplier Performance**: Update supplier metrics based on delivery performance
- **Receiving Reports**: Generate detailed reports of receiving activity

#### Order Management (Phase 3)

The order management process is implemented through the OrderService:

**Key Features**
- **Order Creation**: Create new customer orders with line items
- **Payment Processing**: Update payment status and information
- **Inventory Allocation**: Assign inventory to orders using FIFO
- **Status Workflow**: Manage order status transitions with validation
- **Order Tracking**: Comprehensive tracking of order processing steps
- **Backorder Handling**: Manage partially fulfilled orders

#### Picking and Packing (Phase 3)

The picking and packing process is implemented through dedicated services:

**PickingService Features**
- **Pick List Generation**: Create pick lists organized by location for efficiency
- **FIFO Integration**: Select inventory following FIFO principles
- **Wave Picking**: Support for multi-order picking waves
- **Inventory Updates**: Update inventory records during picking
- **Status Tracking**: Track picking status by order and item

**PackingService Features**
- **Packing Confirmation**: Record packing of picked items
- **Container Management**: Track packaging materials and containers
- **Packing Recommendations**: Suggest appropriate packaging based on items
- **Weight and Dimensions**: Record shipment weight and dimensions
- **Status Updates**: Update order status through packing workflow

#### Shipping Integration (Phase 3)

The shipping process is implemented through an abstraction layer:

**ShippingService Features**
- **Provider Abstraction**: Support multiple shipping carriers (UPS, FedEx, USPS, etc.)
- **Rate Calculation**: Retrieve shipping rates from carriers
- **Label Generation**: Generate shipping labels and documentation
- **Tracking Integration**: Update shipment tracking information
- **Manual Shipping**: Support for manually processed shipments
- **Status Updates**: Update order status through shipping workflow

### API Controllers and Endpoints

The system provides a comprehensive REST API for all operations:

#### Phase 2: Inventory Management

**Product Management**
- `GET /api/products` - List products with filtering and pagination
- `POST /api/products` - Create a new product
- `GET /api/products/{id}` - Get product details
- `PUT /api/products/{id}` - Update a product
- `DELETE /api/products/{id}` - Delete a product
- `GET /api/products/{id}/inventory` - Get inventory for a product

**Category Management**
- `GET /api/categories` - List categories with filtering and pagination
- `GET /api/categories/tree` - Get categories in hierarchical structure
- `POST /api/categories` - Create a new category
- `GET /api/categories/{id}` - Get category details
- `PUT /api/categories/{id}` - Update a category
- `DELETE /api/categories/{id}` - Delete a category

**Warehouse Management**
- `GET /api/warehouses` - List warehouses
- `POST /api/warehouses` - Create a new warehouse
- `GET /api/warehouses/{id}` - Get warehouse details
- `PUT /api/warehouses/{id}` - Update a warehouse
- `DELETE /api/warehouses/{id}` - Delete a warehouse
- `GET /api/warehouses/{id}/zones` - Get zones for a warehouse
- `GET /api/warehouses/{id}/bin-locations` - Get bin locations for a warehouse

**Zone Management**
- `GET /api/zones` - List zones with filtering
- `POST /api/zones` - Create a new zone
- `GET /api/zones/{id}` - Get zone details
- `PUT /api/zones/{id}` - Update a zone
- `DELETE /api/zones/{id}` - Delete a zone
- `GET /api/zones/{id}/bin-locations` - Get bin locations for a zone

**Bin Location Management**
- `GET /api/bin-locations` - List bin locations with filtering
- `POST /api/bin-locations` - Create a new bin location
- `GET /api/bin-locations/{id}` - Get bin location details
- `PUT /api/bin-locations/{id}` - Update a bin location
- `DELETE /api/bin-locations/{id}` - Delete a bin location
- `GET /api/bin-locations/{id}/inventory` - Get inventory for a bin location

**Inventory Management**
- `GET /api/inventory` - List inventory with filtering and pagination
- `GET /api/inventory/summary` - Get inventory summary statistics
- `GET /api/inventory/movements` - List inventory movements with filtering
- `POST /api/inventory/receive` - Receive inventory
- `POST /api/inventory/transfer` - Transfer inventory between locations
- `POST /api/inventory/pick` - Pick inventory (consume with FIFO)
- `POST /api/inventory/adjust` - Adjust inventory quantity

#### Phase 3: Receiving and Order Processing

**Supplier Management**
- `GET /api/suppliers` - List suppliers with filtering
- `POST /api/suppliers` - Create a new supplier
- `GET /api/suppliers/{id}` - Get supplier details
- `PUT /api/suppliers/{id}` - Update a supplier
- `DELETE /api/suppliers/{id}` - Delete a supplier

**Purchase Order Management**
- `GET /api/purchase-orders` - List purchase orders with filtering
- `POST /api/purchase-orders` - Create a new purchase order
- `GET /api/purchase-orders/{id}` - Get purchase order details
- `PUT /api/purchase-orders/{id}` - Update a purchase order
- `DELETE /api/purchase-orders/{id}` - Delete a purchase order
- `POST /api/purchase-orders/{id}/approve` - Approve a purchase order
- `POST /api/purchase-orders/{id}/send` - Send a purchase order to supplier
- `POST /api/purchase-orders/{id}/cancel` - Cancel a purchase order

**Receiving**
- `GET /api/receiving/purchase-orders` - List receivable purchase orders
- `GET /api/receiving/purchase-orders/{id}` - Get purchase order for receiving
- `POST /api/receiving/purchase-orders/{id}/receive` - Receive items for a purchase order
- `POST /api/receiving/purchase-orders/{id}/reject` - Reject items for a purchase order
- `POST /api/receiving/purchase-orders/{id}/close` - Close a purchase order
- `POST /api/receiving/purchase-orders/{id}/quality-inspection` - Process quality inspection
- `GET /api/receiving/purchase-orders/{id}/report` - Generate receiving report

**Order Management**
- `GET /api/orders` - List orders with filtering
- `POST /api/orders` - Create a new order
- `GET /api/orders/{id}` - Get order details
- `PUT /api/orders/{id}` - Update an order
- `POST /api/orders/{id}/payment` - Process payment for an order
- `POST /api/orders/{id}/allocate` - Allocate inventory for an order
- `POST /api/orders/{id}/cancel` - Cancel an order
- `POST /api/orders/{id}/assign` - Assign an order to a user
- `GET /api/orders/ready-for-picking` - Get orders ready for picking
- `GET /api/orders/{id}/pick-list` - Generate pick list for an order

**Picking**
- `POST /api/picking/record` - Record an individual pick
- `POST /api/picking/batch` - Record multiple picks in a batch
- `POST /api/picking/orders/{orderId}/complete` - Complete picking for an order
- `POST /api/picking/wave` - Generate a wave pick list for multiple orders

**Packing**
- `POST /api/packing/item` - Pack an individual order item
- `POST /api/packing/orders/{orderId}/items` - Pack multiple items for an order
- `POST /api/packing/orders/{orderId}/complete` - Complete packing for an order
- `GET /api/packing/orders/{orderId}/recommendations` - Get packing recommendations

**Shipping**
- `GET /api/shipping/orders/{orderId}/rates` - Get shipping rates for an order
- `POST /api/shipping/orders/{orderId}/ship` - Ship an order using a carrier
- `POST /api/shipping/orders/{orderId}/manual-ship` - Manually mark an order as shipped
- `GET /api/shipping/orders/{orderId}/track` - Track a shipped order

### Request Validation and Permission System

The system implements comprehensive request validation and permission checking:

#### Base Form Request
An abstract `BaseFormRequest` class handles authorization for all request classes:
- Extends Laravel's `FormRequest`
- Checks if the user is authenticated
- Verifies if the user has the required permission through their roles
- Centralizes authorization logic to reduce duplication

#### Permission-Based Request Classes
Each API endpoint has a corresponding request validation class that:
- Extends the `BaseFormRequest` class
- Specifies the required permission via the `$permission` property
- Defines validation rules specific to the request
- Provides custom error messages for validation failures

#### Role-Based Permissions
The permission system follows a role-based access control (RBAC) approach:
- Permissions are stored in the `permissions` table with slugs (e.g., "orders.create")
- Roles are assigned collections of permissions via the `permission_role` table
- Users are assigned roles via the `role_user` table
- Permission checks traverse these relationships to determine access

#### Permission Middleware
A custom middleware provides route-level permission checking:
- Registered as `permission` in the kernel
- Used in route definitions: `->middleware('permission:orders.create')`
- Checks if the authenticated user has the specified permission
- Returns appropriate HTTP responses for unauthorized access

## Setting Up Development Environment

1. **Clone the repository**:
   ```bash
   git clone https://your-repository-url/wms-project.git
   cd wms-project
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**:
   Update `.env` file with database settings:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=wms_central
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   # For sessions
   SESSION_DRIVER=database
   ```

5. **Run migrations**:
   ```bash
   # Run central migrations
   php artisan migrate
   
   # Create tenant-specific migrations directory if it doesn't exist
   mkdir -p database/migrations/tenant
   
   # Create sessions migration for tenants
   php artisan make:migration create_sessions_table --path=database/migrations/tenant
   
   # Fill in the sessions migration with the appropriate schema
   
   # Create a development tenant
   php artisan tenant:create-dev --name="Demo Company" --domain=demo
   ```

6. **Seed the database with permissions**:
   ```bash
   php artisan db:seed --class=PermissionsSeeder
   ```

7. **Start development servers**:
   ```bash
   # Terminal 1: Laravel backend
   php artisan serve
   
   # Terminal 2: Vue frontend
   npm run dev
   ```

## Next Development Session Focus

The immediate next steps in development are:

1. **Begin implementing Phase 4**:
   - Set up Vue router and navigation
   - Build dashboard views
   - Create inventory management screens
   - Develop receiving interface
   - Build order management UI
   - Create warehouse visualization components

2. **Extend the permission system**:
   - Create user interface for role and permission management
   - Implement tenant-specific permission overrides
   - Add audit logging for permission-related actions

---

This documentation will help maintain continuity across multiple development sessions. Reference it at the beginning of new sessions to provide context for continued development.