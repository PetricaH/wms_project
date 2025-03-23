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

### Phase 1: Project Setup and Foundation
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

### Phase 2: Core Inventory Models (IN PROGRESS)
- [ ] Design and create inventory database structure
  - [ ] Products table migration and model
  - [ ] Categories table migration and model
  - [ ] Inventory table migration and model
  - [ ] Inventory movements table migration and model
  - [ ] Locations table migration and model
- [ ] Implement warehouse structure models
  - [ ] Warehouses table migration and model
  - [ ] Zones table migration and model
  - [ ] Bin locations table migration and model
- [ ] Create inventory movement service
  - [ ] Implement interfaces and base classes
  - [ ] Create FIFO inventory strategy class
  - [ ] Implement stock movement recording logic
  - [ ] Create inventory transaction history logging
- [ ] Build inventory API endpoints
  - [ ] Inventory listing endpoint with filtering
  - [ ] Inventory movement endpoints
  - [ ] Product CRUD endpoints
  - [ ] Location CRUD endpoints
- [ ] Write unit tests for inventory models and services
  - [ ] Test FIFO logic with various scenarios
  - [ ] Test inventory movement calculations
  - [ ] Test stock level tracking

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

### Authentication System

The authentication system uses Laravel Sanctum for API token management:

#### Key Components:
- **AuthController**: Handles registration, login, and token management
- **Middleware**: `auth:sanctum` for protected API routes
- **Frontend**: JWT token storage and handling in Pinia store

### Database Structure

#### Core Tables
- `tenants` - Tenant information (UUID-based identifiers)
- `domains` - Domain/subdomain routing for tenants
- `users` - User accounts with tenant association
- `roles` - User roles with tenant association
- `permissions` - System permissions (global)
- `permission_role` - Junction table for role permissions
- `role_user` - Junction table for user roles

#### Tenant-Specific Tables
For each tenant database:
- `users` - User data specific to the tenant
- `roles` - Roles specific to the tenant
- `permissions` - Permission definitions
- `sessions` - Session data
- `personal_access_tokens` - API tokens

### Frontend Architecture

#### Key Components:
- **Vue Router**: Handles navigation and route protection
- **Pinia Stores**: State management, particularly for authentication
- **Axios Service**: API communication with interceptors for auth tokens
- **Layouts**: Layout components for different page types
- **Views**: Page components for different application sections

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
   ```

5. **Run migrations**:
   ```bash
   php artisan migrate
   ```

6. **Create a development tenant**:
   ```bash
   php artisan tenant:create-dev --name="Demo Company" --domain=demo
   ```
   This creates:
   - A tenant record in the central database
   - A tenant-specific database (`tenant_demo`)
   - Required tables in the tenant database
   - Initial user and role data

7. **Start development servers**:
   ```bash
   # Terminal 1: Laravel backend
   php artisan serve
   
   # Terminal 2: Vue frontend
   npm run dev
   ```

## Next Development Session Focus

The immediate next steps in development are:

1. **Create product and inventory models**:
   - Implement product management with categories
   - Build inventory tracking system with FIFO logic
   - Develop location management

2. **Build API endpoints**:
   - Product CRUD operations
   - Inventory movement actions
   - Location management

3. **Develop frontend components**:
   - Product management screens
   - Inventory level displays
   - Basic warehouse visualization

## Common Issues and Solutions

### Tenant Database Issues
- **Missing tables in tenant database**:
  - Use the SQL script or create-dev command to properly set up tenant databases
  - Ensure migrations run correctly in the tenant context

### Authentication Problems
- **Token issues**:
  - Check correct token storage in localStorage
  - Verify Axios interceptors are properly configured

### Development Setup
- **Multiple databases**:
  - In development, you can use a single database for all tenants
  - In production, each tenant should have its own database

---

This documentation will help maintain continuity across multiple development sessions. Reference it at the beginning of new sessions to provide context for continued development.