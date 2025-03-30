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

### Phase 3: Receiving and Order Processing (PENDING)
- [ ] Create supplier and purchasing models
- [ ] Build receiving workflow
- [ ] Implement order management
- [ ] Create picking and packing workflow
- [ ] Implement shipping integration framework

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

### Authentication System

The authentication system uses Laravel Sanctum for API token management:

#### Key Components:
- **AuthController**: Handles registration, login, and token management
- **Middleware**: `auth:sanctum` for protected API routes
- **Frontend**: JWT token storage and handling in Pinia store

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

#### Tenant-Specific Tables
For each tenant database:
- `users` - User data specific to the tenant
- `roles` - Roles specific to the tenant
- `permissions` - Permission definitions
- `sessions` - Session data
- `personal_access_tokens` - API tokens

### Inventory Models and Relationships

#### Product and Category Models

**Category Model**
- Belongs to a parent category (self-referencing)
- Has many child categories (self-referencing)
- Has many products
- Includes scopes for filtering active categories

**Product Model**
- Belongs to a category
- Has many inventory records
- Has many inventory movements
- Includes accessors for total, available, and reserved quantities
- Includes scopes for filtering active products

#### Warehouse Structure Models

**Warehouse Model**
- Has many zones
- Has many bin locations through zones
- Has many inventory items through bin locations
- Includes scopes for filtering active warehouses

**Zone Model**
- Belongs to a warehouse
- Has many bin locations
- Has many inventory items through bin locations
- Includes scopes for filtering active zones

**BinLocation Model**
- Belongs to a zone
- Has many inventory records
- Has relationship accessors to warehouse through zone
- Includes formatted position and full path accessors
- Includes scopes for filtering active locations

#### Inventory Models

**Inventory Model**
- Belongs to a product
- Belongs to a bin location
- Automatically calculates available_quantity based on quantity and reserved_quantity
- Includes methods for checking expiry status
- Includes scopes for filtering by stock status, availability, and expiry

**InventoryMovement Model**
- Records movement of inventory between locations or in/out of the system
- Includes movement types (receive, transfer, pick, adjust, return, scrap)
- Belongs to a product
- Belongs to source and destination locations
- Has many transactions
- Stores FIFO layer information for tracking oldest inventory
- Includes scopes for filtering by type, reference, and date range

**InventoryTransaction Model**
- Records detailed transaction history for audit purposes
- Belongs to a movement
- Belongs to a product and location
- Tracks running balance at each transaction
- Includes transaction types (increment, decrement, adjust, reserve, unreserve)
- Includes scopes for filtering by type and date range

### Inventory Movement Strategy

The system implements a FIFO (First In, First Out) inventory strategy through a service layer:

#### Strategy Interface and Base Class

**InventoryStrategyInterface**
- Defines contract for inventory operations:
  - receive - Add inventory to a location
  - transfer - Move inventory between locations
  - pick - Remove inventory from a location
  - adjust - Adjust inventory quantity
  - reserve - Reserve inventory for future use
  - unreserve - Release reserved inventory

**BaseInventoryStrategy**
- Implements common functionality for all inventory strategies:
  - Creating movement and transaction records
  - Finding or creating inventory records
  - Updating inventory quantities
  - Database transaction handling

#### FIFO Implementation

**FifoInventoryStrategy**
- Implements the FIFO inventory strategy:
  - Tracks inventory by received date
  - Ensures oldest inventory is consumed first
  - Records FIFO layers when picking or transferring
  - Maintains complete transaction history
  - Enforces inventory availability constraints

Key Features:
- **Receiving**: Creates new inventory records with received date
- **Picking**: Consumes inventory starting with oldest items first
- **Transferring**: Maintains FIFO order when moving between locations
- **Adjusting**: Updates inventory with proper audit trail
- **Reserving**: Reserves inventory following FIFO principles
- **Transaction Tracking**: Every operation generates detailed transaction records

### API Controllers and Endpoints

The system provides a comprehensive REST API for inventory management:

#### Product Management

**ProductController**
- `GET /api/products` - List products with filtering and pagination
- `POST /api/products` - Create a new product
- `GET /api/products/{id}` - Get product details
- `PUT /api/products/{id}` - Update a product
- `DELETE /api/products/{id}` - Delete a product
- `GET /api/products/{id}/inventory` - Get inventory for a product

#### Category Management

**CategoryController**
- `GET /api/categories` - List categories with filtering and pagination
- `GET /api/categories/tree` - Get categories in hierarchical structure
- `POST /api/categories` - Create a new category
- `GET /api/categories/{id}` - Get category details
- `PUT /api/categories/{id}` - Update a category
- `DELETE /api/categories/{id}` - Delete a category

#### Warehouse Management

**WarehouseController**
- `GET /api/warehouses` - List warehouses
- `POST /api/warehouses` - Create a new warehouse
- `GET /api/warehouses/{id}` - Get warehouse details
- `PUT /api/warehouses/{id}` - Update a warehouse
- `DELETE /api/warehouses/{id}` - Delete a warehouse
- `GET /api/warehouses/{id}/zones` - Get zones for a warehouse
- `GET /api/warehouses/{id}/bin-locations` - Get bin locations for a warehouse

**ZoneController**
- `GET /api/zones` - List zones with filtering
- `POST /api/zones` - Create a new zone
- `GET /api/zones/{id}` - Get zone details
- `PUT /api/zones/{id}` - Update a zone
- `DELETE /api/zones/{id}` - Delete a zone
- `GET /api/zones/{id}/bin-locations` - Get bin locations for a zone

**BinLocationController**
- `GET /api/bin-locations` - List bin locations with filtering
- `POST /api/bin-locations` - Create a new bin location
- `GET /api/bin-locations/{id}` - Get bin location details
- `PUT /api/bin-locations/{id}` - Update a bin location
- `DELETE /api/bin-locations/{id}` - Delete a bin location
- `GET /api/bin-locations/{id}/inventory` - Get inventory for a bin location

#### Inventory Management

**InventoryController**
- `GET /api/inventory` - List inventory with filtering and pagination
- `GET /api/inventory/summary` - Get inventory summary statistics
- `GET /api/inventory/movements` - List inventory movements with filtering
- `POST /api/inventory/receive` - Receive inventory
- `POST /api/inventory/transfer` - Transfer inventory between locations
- `POST /api/inventory/pick` - Pick inventory (consume with FIFO)
- `POST /api/inventory/adjust` - Adjust inventory quantity

### Validation and Request Handling

The system implements comprehensive request validation for all operations:

**Request Validation Classes**
- `ProductRequest` - Product creation/update validation
- `CategoryRequest` - Category creation/update validation
- `WarehouseRequest` - Warehouse creation/update validation
- `ZoneRequest` - Zone creation/update validation
- `BinLocationRequest` - Bin location creation/update validation
- `InventoryReceiveRequest` - Inventory receipt validation
- `InventoryMoveRequest` - Inventory transfer validation
- `InventoryPickRequest` - Inventory picking validation
- `InventoryAdjustRequest` - Inventory adjustment validation

Key Validation Features:
- Proper data type validation
- Required field validation
- Unique constraints with database scoping
- Relationship validation (foreign keys exist)
- Custom validation for business rules
- Error messaging

### Testing Infrastructure

The system includes comprehensive unit tests for inventory functionality:

**Test Classes**
- `FifoInventoryStrategyTest` - Tests FIFO inventory strategy implementation
- `InventoryModelTest` - Tests inventory model behavior
- `InventoryMovementTest` - Tests inventory movement functionality

**Factory Classes**
- `ProductFactory` - Creates test product data
- `CategoryFactory` - Creates test category data
- `WarehouseFactory` - Creates test warehouse data
- `ZoneFactory` - Creates test zone data
- `BinLocationFactory` - Creates test bin location data
- `InventoryFactory` - Creates test inventory records
- `InventoryMovementFactory` - Creates test movement records

### Frontend Architecture

#### Key Components:
- **Vue Router**: Handles navigation and route protection
- **Pinia Stores**: State management, particularly for authentication
- **Axios Service**: API communication with interceptors for auth tokens
- **Layouts**: Layout components for different page types
- **Views**: Page components for different application sections

## Common Issues and Solutions

### Migration Order Issues
- **Problem**: Foreign key constraints failing during migrations
- **Solution**: Ensure migrations run in the correct order by adjusting timestamps:
  1. Create warehouses table before zones
  2. Create zones table before bin_locations
  3. Create products and bin_locations before inventory
  4. Create inventory before inventory_movements
  5. Create inventory_movements before inventory_transactions

### Table Naming Conventions
- **Problem**: Foreign key constraint errors due to inconsistent table naming
- **Solution**: Use plural table names consistently:
  - Use `bin_locations` instead of `bin_location`
  - Ensure model `$table` properties match migration table names
  - Update foreign key references to use the correct table names

### Tenant-Specific Tables
- **Problem**: Missing required tables in tenant databases
- **Solution**: Create tenant-specific migrations in `database/migrations/tenant`:
  - Ensure `sessions` table is created for tenant databases
  - Run tenant migrations with `php artisan tenants:migrate`

### Authentication Issues
- **Problem**: Token authentication failing
- **Solution**: Check Sanctum configuration and token handling:
  - Verify Sanctum guard is properly configured
  - Ensure token storage and retrieval in frontend
  - Check API routes are properly protected with middleware

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

6. **Start development servers**:
   ```bash
   # Terminal 1: Laravel backend
   php artisan serve
   
   # Terminal 2: Vue frontend
   npm run dev
   ```

## Next Development Session Focus

The immediate next steps in development are:

1. **Begin implementing Phase 3**:
   - Create supplier management models and controllers
   - Build receiving workflow
   - Implement order management system
   - Integrate with inventory management

2. **Start frontend development**:
   - Create inventory management screens
   - Build warehouse visualization
   - Implement product management UI
   - Develop inventory movement operations UI

---

This documentation will help maintain continuity across multiple development sessions. Reference it at the beginning of new sessions to provide context for continued development.