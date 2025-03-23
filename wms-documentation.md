# WMS Project Documentation

This document provides an overview of the Warehouse Management System (WMS) project structure, key components, and implementation details to help continue development across multiple chat sessions.

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

## Current Implementation Status

### Phase 1: Project Setup and Foundation (COMPLETED)
- [x] Laravel 11 project created
- [x] Vue.js and Vite configured
- [x] Tailwind CSS integrated
- [x] Multi-tenancy implemented using stancl/tenancy package
- [x] Database migrations created for all system tables
- [x] Configured central and tenant database connections
- [x] Set up tenant middleware for request handling
- [x] Created core database migrations (tenants, domains, users, roles, permissions)
- [x] Implemented database seeders for testing data
- [x] Set up authentication system with Laravel Sanctum
- [x] Created login/register screens in Vue
- [x] Implemented JWT token handling on frontend with Pinia store

### Phase 2: Core Inventory Models (NEXT PHASE)
- [ ] Design and create inventory database structure
  - [ ] Products table migration and model
  - [ ] Categories table migration and model
  - [ ] Inventory table migration and model
  - [ ] Inventory movements table migration and model
  - [ ] Locations table migration and model
- [ ] Implement warehouse structure models
- [ ] Create inventory movement service with FIFO implementation
- [ ] Build inventory API endpoints
- [ ] Write unit tests for inventory models and services

## Key Files and Components

### Backend Structure

1. **Multi-tenancy**
   - `app/Models/Tenant.php` - Tenant model
   - `app/Models/Domain.php` - Domain model for subdomain routing
   - `app/Http/Middleware/TenantMiddleware.php` - Custom middleware for tenant context

2. **Authentication**
   - `app/Http/Controllers/API/AuthController.php` - Handles user registration, login, logout
   - `routes/api.php` - API routes for authentication

3. **Core Models**
   - `app/Models/User.php` - User model with role relationships
   - `app/Models/Role.php` - Role model with permissions
   - `app/Models/Permission.php` - Permission model

4. **Database Migrations**
   - `database/migrations/xxxx_xx_xx_create_tenants_table.php`
   - `database/migrations/xxxx_xx_xx_create_domains_table.php`
   - `database/migrations/xxxx_xx_xx_create_users_table.php`
   - `database/migrations/xxxx_xx_xx_create_roles_table.php`
   - `database/migrations/xxxx_xx_xx_create_permissions_table.php`
   - `database/migrations/xxxx_xx_xx_create_permission_role_table.php`
   - `database/migrations/xxxx_xx_xx_create_role_user_table.php`

5. **Database Seeders**
   - `database/seeders/DatabaseSeeder.php` - Master seeder
   - `database/seeders/TenantSeeder.php` - Creates test tenants, users, roles, and permissions

### Frontend Structure

1. **Main Files**
   - `resources/js/app.js` - Vue application entry point
   - `resources/js/App.vue` - Main application component
   - `resources/js/router/index.js` - Vue Router configuration

2. **State Management**
   - `resources/js/stores/auth.js` - Pinia store for authentication

3. **API Service**
   - `resources/js/services/axios.js` - Axios configuration with interceptors

4. **Layouts**
   - `resources/js/layouts/DefaultLayout.vue` - Main application layout
   - `resources/js/layouts/AuthLayout.vue` - Authentication pages layout
   - `resources/js/layouts/ErrorLayout.vue` - Error pages layout

5. **Views**
   - `resources/js/views/Dashboard.vue` - Main dashboard after login
   - `resources/js/views/auth/Login.vue` - Login form
   - `resources/js/views/auth/Register.vue` - Registration form
   - `resources/js/views/NotFound.vue` - 404 page

## Database Structure

### Core Tables
- `tenants` - Tenant information with UUID as ID and JSON data column
- `domains` - Domain/subdomain routing for tenants
- `users` - User accounts with tenant association
- `roles` - User roles with tenant association
- `permissions` - System permissions (global)
- `permission_role` - Junction table for role permissions
- `role_user` - Junction table for user roles
- `personal_access_tokens` - Sanctum API tokens

### Planned Tables for Phase 2
```
products:
- id
- tenant_id (foreign key to tenants table)
- sku
- name
- description
- category_id
- attributes (JSON)
- created_at
- updated_at

categories:
- id
- tenant_id
- name
- slug
- description
- parent_id
- created_at
- updated_at

inventory:
- id
- tenant_id
- product_id
- location_id
- batch_number
- received_date
- quantity
- unit_cost
- created_at
- updated_at

inventory_movements:
- id
- tenant_id
- product_id
- from_location_id
- to_location_id
- quantity
- movement_type (receiving, picking, adjustment)
- reference_type (order, purchase, etc.)
- reference_id
- batch_number
- movement_date
- created_by
- created_at
- updated_at

locations:
- id
- tenant_id
- warehouse_id
- zone_id
- name
- code
- type (receiving, storage, shipping)
- created_at
- updated_at
```

## Multi-tenancy Implementation Details

The project uses the `stancl/tenancy` package for multi-tenancy:

1. **Tenant Structure**:
   - `tenants` table: Stores tenant information with UUID as ID
   - `data` column: JSON column that stores tenant metadata (name, database, etc.)
   - `domains` table: Handles subdomain routing for tenants

2. **Tenant Resolution**:
   - The package middleware handles tenant resolution based on domain
   - Our custom `TenantMiddleware` adds additional functionality for API requests

3. **Model Scoping**:
   - Models use tenant_id for filtering data in multi-tenant context
   - Global scopes are defined to ensure proper tenant isolation

4. **Frontend Implementation**:
   - Tenant ID included in API requests via Axios interceptors
   - Tenant context stored in Pinia auth store

## Authentication System

1. **Backend**:
   - Laravel Sanctum for token-based authentication
   - `AuthController` with register, login, user info, and logout methods
   - Protected routes with `auth:sanctum` middleware

2. **Frontend**:
   - JWT token stored in localStorage and Pinia store
   - Axios interceptors add token to requests
   - Vue Router navigation guards for protected routes
   - Login and Register forms with error handling

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
   DB_DATABASE=wms_project
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**:
   ```bash
   php artisan migrate
   ```

6. **Seed the database**:
   ```bash
   php artisan db:seed
   ```

7. **Start development servers**:
   ```bash
   # Terminal 1: Laravel backend
   php artisan serve
   
   # Terminal 2: Vue frontend
   npm run dev
   ```

## Project Roadmap

### Phase 1: Project Setup and Foundation (COMPLETED)
- Basic project structure and tech stack setup
- Multi-tenancy implementation
- Authentication system
- Core database migrations

### Phase 2: Core Inventory Models (NEXT)
- Product and category management
- Inventory tracking with FIFO
- Warehouse location management
- Inventory movement tracking

### Phase 3: Receiving and Order Processing
- Supplier and purchasing models
- Receiving workflow
- Order management
- Picking and packing workflow
- Shipping integration

### Phase 4: Frontend UI Development
- Dashboard and visualization
- Inventory management screens
- Receiving interface
- Order management UI
- Warehouse management components

### Phase 5: Business Adaptability Features
- Multi-business settings
- Rules engine for business logic
- Integration framework
- Reporting system
- User and role management

### Phase 6: Mobile Optimization
- Responsive UI improvements
- Barcode scanning
- Mobile-specific workflows

### Phase 7: System Optimization and Testing
- Performance optimization
- Comprehensive testing
- Documentation
- Security audit

### Phase 8: Productization and Launch Preparation
- Subscription management (if SaaS)
- White-labeling features
- Onboarding flow
- Deployment process

### Phase 9: Launch and Post-Launch
- Final QA testing
- Production deployment
- Analytics and monitoring
- Customer support system

## Useful Commands

```bash
# Create migrations
php artisan make:migration create_products_table

# Create models
php artisan make:model Product -m

# Create controllers
php artisan make:controller API/ProductController --api

# Create Pinia store
# (manually create in resources/js/stores directory)

# Create Vue component
# (manually create in resources/js/views or components directory)

# Run database migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Create a new tenant (via Tinker)
php artisan tinker
Tenant::create(['id' => Str::uuid(), 'data' => ['name' => 'New Tenant', 'database' => 'tenant_new']])
```

## Common Issues and Solutions

1. **Migration Order Issues**:
   - Ensure migrations are created in the correct order
   - Foreign key constraints require referenced tables to exist first
   - Use `php artisan migrate:refresh` to redo migrations if needed

2. **Tenant Structure Changes**:
   - The `tenants` table uses a JSON `data` column for tenant details
   - Tenant name is stored in the JSON data, not as a separate column
   - Use `$tenant->data['name']` to access the tenant name

## Next Development Session Focus

For the next session, focus on:
1. Creating product and category models and migrations
2. Implementing basic CRUD operations for products
3. Setting up the FIFO inventory service

---

This documentation will help maintain continuity across multiple development sessions with Claude. Reference it at the beginning of new sessions to provide context for continued development.