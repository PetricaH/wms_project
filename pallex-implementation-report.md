# Pallex Implementation Report
## Warehouse Management System Integration Research

**Date**: December 3, 2025
**Project**: WMS Multi-Tenant Application
**Subject**: Pallex Freight Network Integration Analysis

---

## Executive Summary

This report provides a comprehensive analysis of what it would mean to implement Pallex integration into the existing WMS (Warehouse Management System). Pallex is a palletized freight distribution network operating across the UK, Europe, and internationally, with over 7,500 vehicles and 600 sub-licensees. Their core operating system, **Nexus**, provides warehouse management capabilities and API integration options.

**Key Finding**: The WMS project already has a robust shipping abstraction layer that can accommodate Pallex integration. Implementation would primarily involve creating a new `PallexShippingProvider` class and configuring API credentials.

---

## 1. What is Pallex?

### 1.1 Business Overview
- **Type**: Palletized freight distribution network
- **Coverage**: UK, Europe, and international markets
- **Network Size**:
  - 7,500+ vehicles
  - 600+ recruited sub-licensees
  - Advanced IT system (Nexus)
- **Specialization**: Pallet-level freight (not parcel-level like UPS/FedEx)

### 1.2 Core Technology: Nexus System
Pallex operates on **NexusWMS**, their proprietary warehouse management system that includes:
- Complete inventory management
- Stock viewing and management
- Consignment booking
- API integration capabilities
- Customs declaration automation
- Real-time shipment tracking

### 1.3 Key Differentiator
Unlike traditional parcel carriers (UPS, FedEx, USPS), Pallex specializes in **palletized freight**, making it ideal for:
- Bulk shipments
- B2B warehouse-to-warehouse transfers
- Large-volume orders
- Cross-border pallet movements
- Distribution center operations

---

## 2. Current WMS Project Architecture

### 2.1 Technology Stack
- **Backend**: Laravel 11 (PHP)
- **Frontend**: Vue.js 3 with Tailwind CSS
- **Database**: MySQL
- **Architecture**: Multi-tenant (stancl/tenancy)
- **Authentication**: Laravel Sanctum

### 2.2 Existing Shipping Integration Framework

The project already has a **shipping service abstraction layer** located at:
```
app/Services/Shipping/ShippingServiceAbstractionLayer.php
```

**Current Architecture**:
```
ShippingProviderBase (Abstract Class)
    ├── MockShippingProvider
    ├── UPSShippingProvider
    ├── FedExShippingProvider
    └── USPSShippingProvider
```

**Available Methods**:
1. `getRates()` - Get shipping rate quotes
2. `createShipment()` - Create a shipment
3. `generateLabel()` - Generate shipping labels
4. `validateAddress()` - Validate shipping addresses
5. `trackShipment()` - Track shipment status
6. `cancelShipment()` - Cancel a shipment

### 2.3 Shipping Service Integration Points
The shipping service integrates with:
- **Order Management System**: Ships orders after packing
- **Inventory System**: Updates inventory upon shipment
- **Tracking System**: Records tracking numbers and carrier info
- **Status Workflow**: Transitions orders through states (packed → shipped → delivered)

---

## 3. Pallex API Integration Capabilities

### 3.1 Available APIs
Based on research, Pallex offers:

1. **MyNexus API**
   - Portal: `mynexus.pallex.com`
   - Authentication: Username/password credentials
   - Integration: RESTful API for shipment management

2. **ClientPlus API**
   - Portal: `clientplus.pallex.ro/api/`
   - Interface: API UI for testing and integration

3. **Integration Features**:
   - Automated shipment booking
   - Customs declaration processing
   - Real-time tracking
   - Rate calculation
   - Label generation
   - Consignment management

### 3.2 API Documentation Access
**Challenge**: Official API documentation is not publicly available. Access requires:
- Contacting Pallex directly for developer credentials
- Accessing MyNexus portal with authorized account
- Working with third-party integration providers (e.g., Canary7, Despatch Cloud)

### 3.3 Third-Party Integration Options
Several WMS providers offer pre-built Pallex integrations:
- **Canary7 WMS**: Complete Pallex integration
- **Despatch Cloud**: Documented Pallex integration guide
- **CustomsPro**: API integration for customs automation

---

## 4. Implementation Requirements

### 4.1 Technical Requirements

#### 4.1.1 New Provider Class
Create `PallexShippingProvider` extending `ShippingProviderBase`:

```php
Location: app/Services/Shipping/Providers/PallexShippingProvider.php

class PallexShippingProvider extends ShippingProviderBase
{
    // Implement required methods:
    - getRates(array $shipmentData): array
    - createShipment(array $shipmentData): array
    - generateLabel(string $shipmentId, array $options): array
    - validateAddress(array $addressData): array
    - trackShipment(string $trackingNumber): array
    - cancelShipment(string $shipmentId): bool
}
```

#### 4.1.2 Configuration Updates
Update `config/shipping.php` (or create if doesn't exist):

```php
'providers' => [
    'pallex' => [
        'enabled' => env('PALLEX_ENABLED', false),
        'api_url' => env('PALLEX_API_URL', 'https://mynexus.pallex.com/api'),
        'username' => env('PALLEX_USERNAME'),
        'password' => env('PALLEX_PASSWORD'),
        'api_key' => env('PALLEX_API_KEY'),
        'account_number' => env('PALLEX_ACCOUNT_NUMBER'),
        'default_service' => env('PALLEX_DEFAULT_SERVICE', 'standard'),
        'timeout' => 30,
    ],
],
```

#### 4.1.3 Environment Variables
Add to `.env`:
```
PALLEX_ENABLED=true
PALLEX_API_URL=https://mynexus.pallex.com/api
PALLEX_USERNAME=your_username
PALLEX_PASSWORD=your_password
PALLEX_API_KEY=your_api_key
PALLEX_ACCOUNT_NUMBER=your_account_number
```

#### 4.1.4 Service Registration
Update `ShippingServiceAbstractionLayer.php` line 94-99:

```php
$providers = [
    'mock' => MockShippingProvider::class,
    'ups' => UPSShippingProvider::class,
    'fedex' => FedExShippingProvider::class,
    'usps' => USPSShippingProvider::class,
    'pallex' => PallexShippingProvider::class, // Add this line
];
```

### 4.2 Database Requirements

#### 4.2.1 Migration: Add Pallet Information
Since Pallex handles pallets (not parcels), extend the `orders` table:

```sql
ALTER TABLE orders ADD COLUMN pallet_count INT DEFAULT 1;
ALTER TABLE orders ADD COLUMN pallet_type VARCHAR(50); -- e.g., 'EUR', 'UK'
ALTER TABLE orders ADD COLUMN total_weight_kg DECIMAL(10,2);
ALTER TABLE orders ADD COLUMN requires_tail_lift BOOLEAN DEFAULT false;
ALTER TABLE orders ADD COLUMN collection_date DATE;
```

#### 4.2.2 Migration: Pallex-Specific Metadata
Add fields to store Pallex-specific data:

```sql
-- The orders table already has a 'metadata' JSON column
-- Use it to store Pallex-specific information:
{
    "pallex": {
        "consignment_number": "PALLEX123456",
        "service_level": "standard",
        "pallet_details": [...],
        "collection_date": "2025-12-03",
        "delivery_date": "2025-12-05"
    }
}
```

### 4.3 Dependencies
Potential PHP packages needed:

```bash
# HTTP Client for API calls (if not using Laravel's built-in)
composer require guzzlehttp/guzzle

# For label PDF generation
composer require barryvdh/laravel-dompdf
```

### 4.4 API Integration Components

#### Required API Endpoints to Implement:

1. **Rate Calculation**
   - Endpoint: `POST /api/rates`
   - Input: Origin, destination, pallet count, weight
   - Output: Available services with pricing

2. **Create Consignment**
   - Endpoint: `POST /api/consignments`
   - Input: Shipment details, pallet information
   - Output: Consignment number, booking confirmation

3. **Generate Labels**
   - Endpoint: `GET /api/consignments/{id}/labels`
   - Output: PDF label for pallets

4. **Track Shipment**
   - Endpoint: `GET /api/consignments/{id}/tracking`
   - Output: Real-time tracking events

5. **Collection Booking**
   - Endpoint: `POST /api/collections`
   - Input: Collection date/time, location
   - Output: Collection reference

---

## 5. Implementation Scope & Effort

### 5.1 Development Tasks

#### Phase 1: Core Integration (High Priority)
1. **API Client Development** (8-16 hours)
   - Create HTTP client wrapper for Pallex API
   - Implement authentication/token management
   - Error handling and logging
   - Request/response serialization

2. **Provider Implementation** (16-24 hours)
   - Create `PallexShippingProvider` class
   - Implement all abstract methods
   - Unit tests for each method
   - Integration tests with Pallex sandbox

3. **Configuration & Setup** (4-8 hours)
   - Configuration files
   - Environment variables
   - Service registration
   - Documentation

4. **Database Extensions** (4-8 hours)
   - Create migrations for pallet-related fields
   - Update Order model with pallet accessors
   - Seed data for testing

#### Phase 2: UI Integration (Medium Priority)
5. **Frontend Updates** (16-24 hours)
   - Add Pallex as shipping option in UI
   - Pallet information input forms
   - Collection date picker
   - Display Pallex-specific tracking info

6. **Order Flow Updates** (8-16 hours)
   - Modify packing workflow for pallets
   - Add pallet counting interface
   - Update shipping method selection
   - Collection scheduling UI

#### Phase 3: Advanced Features (Lower Priority)
7. **Customs Integration** (16-24 hours)
   - Customs declaration forms
   - Commercial invoice generation
   - Harmonized codes management
   - Cross-border compliance

8. **Reporting & Analytics** (8-16 hours)
   - Pallex shipment reports
   - Cost analysis by service level
   - Performance metrics (on-time delivery)
   - Pallet utilization tracking

### 5.2 Total Estimated Effort
- **Minimum**: 56 hours (~1.5 weeks)
- **Maximum**: 120 hours (~3 weeks)
- **Realistic**: 80-90 hours (~2-2.5 weeks)

### 5.3 Prerequisites Before Starting
1. **Obtain Pallex API Access**
   - Contact Pallex sales/support
   - Obtain developer credentials
   - Access to sandbox/test environment
   - API documentation

2. **Business Requirements**
   - Define supported service levels
   - Determine pallet types to support
   - Collection scheduling requirements
   - Label printing requirements

3. **Testing Environment**
   - Pallex test account
   - Test orders with various scenarios
   - Printer for label testing (if physical)

---

## 6. Benefits of Pallex Integration

### 6.1 Operational Benefits
- **Bulk Shipping**: Handle large orders more efficiently
- **Cost Savings**: Pallet freight typically cheaper than multiple parcels
- **B2B Focus**: Better suited for warehouse-to-warehouse transfers
- **European Reach**: Strong presence in UK and Europe
- **Network Size**: Access to 7,500+ vehicles

### 6.2 Customer Benefits
- **Flexibility**: Multiple shipping options (parcel vs. pallet)
- **Tracking**: Real-time visibility of pallet shipments
- **Reliability**: Established freight network
- **International**: Simplified cross-border shipments

### 6.3 Competitive Advantages
- **Multi-Modal**: Support both parcel and freight shipping
- **Comprehensive WMS**: Full-service warehouse solution
- **Scalability**: Handle both small and large orders
- **Market Expansion**: Target B2B customers requiring bulk shipping

---

## 7. Challenges & Considerations

### 7.1 Technical Challenges
1. **API Documentation**: Limited public documentation
2. **Authentication**: May require complex OAuth or token management
3. **Data Mapping**: Pallet shipping has different data requirements than parcel
4. **Testing**: Requires Pallex test environment access
5. **Error Handling**: Freight APIs may have unique error scenarios

### 7.2 Business Challenges
1. **Account Setup**: Requires business relationship with Pallex
2. **Pricing Model**: Freight pricing more complex than parcel
3. **Service Areas**: Pallex coverage different from parcel carriers
4. **Lead Times**: Collection scheduling adds complexity
5. **Minimum Requirements**: May have minimum pallet or weight requirements

### 7.3 Integration Considerations
1. **Order Type Detection**: Need logic to determine parcel vs. pallet shipping
2. **Pallet Calculation**: Algorithm to calculate optimal pallet count
3. **Weight Calculation**: Accurate total weight calculation
4. **Address Validation**: Business addresses vs. residential
5. **Collection Scheduling**: Integration with warehouse operations

---

## 8. Alternative Approaches

### 8.1 Manual Pallex Integration
**Approach**: Use existing "manual shipping" feature
- Users manually book with Pallex
- Enter tracking number into WMS
- No API integration required
- **Effort**: 0 hours (already supported)
- **Limitation**: No automation, no rate quotes

### 8.2 Third-Party Integration Platform
**Options**:
- **Canary7 WMS**: Pre-built Pallex integration
- **Despatch Cloud**: Shipping middleware
- **ShipStation**: Multi-carrier platform

**Pros**:
- Faster implementation
- Maintained by third party
- May support multiple carriers

**Cons**:
- Monthly fees
- Less control
- Data flows through third party

### 8.3 Phased Approach
**Phase 1**: Manual integration (use existing features)
**Phase 2**: Rate quote API only
**Phase 3**: Full booking automation
**Phase 4**: Advanced features (customs, reporting)

**Benefit**: Incremental value delivery, reduced risk

---

## 9. Recommendations

### 9.1 Immediate Actions
1. **Contact Pallex** to discuss:
   - API access and documentation
   - Developer credentials
   - Sandbox environment
   - Technical support

2. **Gather Requirements**:
   - Survey potential customers on pallet shipping needs
   - Determine service level requirements
   - Define MVP feature set

3. **Evaluate Alternatives**:
   - Compare in-house development vs. third-party integration
   - Consider cost/benefit analysis
   - Assess maintenance implications

### 9.2 Implementation Approach
**Recommended**: Phased implementation

**Phase 1 (Week 1-2)**: Core API Integration
- PallexShippingProvider implementation
- Basic rate quotes
- Shipment creation
- Label generation

**Phase 2 (Week 3)**: Testing & Refinement
- Integration testing with Pallex sandbox
- Error handling improvements
- Documentation

**Phase 3 (Week 4+)**: UI & Advanced Features
- Frontend updates
- Collection scheduling
- Customs integration (if needed)

### 9.3 Risk Mitigation
1. **Start with Mock Implementation**: Create `MockPallexProvider` first
2. **Use Feature Flags**: Enable/disable Pallex per tenant
3. **Comprehensive Logging**: Log all API interactions
4. **Fallback Options**: Maintain manual shipping capability
5. **Gradual Rollout**: Test with select customers first

---

## 10. Cost Analysis

### 10.1 Development Costs
| Item | Estimated Hours | Rate | Total |
|------|----------------|------|-------|
| Core API Integration | 30-40 | Variable | - |
| Provider Implementation | 20-30 | Variable | - |
| Frontend Updates | 20-25 | Variable | - |
| Testing & QA | 15-20 | Variable | - |
| Documentation | 5-10 | Variable | - |
| **Total** | **90-125 hours** | - | - |

### 10.2 Operational Costs
- **Pallex Account**: Varies (contact Pallex)
- **API Fees**: Unknown (may be transaction-based)
- **Maintenance**: ~5-10 hours/month
- **Support**: Included in development or separate

### 10.3 Return on Investment
**Potential Revenue**:
- Target customers requiring bulk shipping
- Premium feature for enterprise tiers
- Competitive differentiator
- Market expansion (UK/Europe B2B)

---

## 11. Technical Architecture Diagram

```
┌─────────────────────────────────────────────────────┐
│                   WMS Application                    │
├─────────────────────────────────────────────────────┤
│                                                      │
│  ┌──────────────────────────────────────────────┐  │
│  │         ShippingController                    │  │
│  │  - getShippingRates()                        │  │
│  │  - shipOrder()                               │  │
│  │  - trackOrderShipment()                      │  │
│  └────────────────┬─────────────────────────────┘  │
│                   │                                  │
│  ┌────────────────▼─────────────────────────────┐  │
│  │         ShippingService                       │  │
│  │  - setProvider()                             │  │
│  │  - getOrderShippingRates()                   │  │
│  │  - shipOrder()                               │  │
│  │  - trackOrderShipment()                      │  │
│  └────────────────┬─────────────────────────────┘  │
│                   │                                  │
│  ┌────────────────▼─────────────────────────────┐  │
│  │      ShippingProviderBase (Abstract)         │  │
│  │  - getRates()                                │  │
│  │  - createShipment()                          │  │
│  │  - generateLabel()                           │  │
│  │  - validateAddress()                         │  │
│  │  - trackShipment()                           │  │
│  │  - cancelShipment()                          │  │
│  └──────┬───────┬────────┬────────┬────────┬────┘  │
│         │       │        │        │        │        │
│    ┌────▼──┐ ┌─▼──┐ ┌──▼───┐ ┌──▼───┐ ┌──▼─────┐ │
│    │ Mock  │ │UPS │ │FedEx │ │USPS  │ │ Pallex │ │
│    │Provider│ │    │ │      │ │      │ │ (NEW)  │ │
│    └───────┘ └────┘ └──────┘ └──────┘ └────┬───┘ │
│                                              │      │
└──────────────────────────────────────────────┼─────┘
                                               │
                            ┌──────────────────▼────────────────┐
                            │     Pallex Nexus API              │
                            │  - MyNexus Portal                 │
                            │  - Rate Calculation               │
                            │  - Consignment Booking            │
                            │  - Label Generation               │
                            │  - Tracking                       │
                            │  - Collection Management          │
                            └───────────────────────────────────┘
```

---

## 12. Testing Strategy

### 12.1 Unit Tests
- Test each method in `PallexShippingProvider`
- Mock API responses
- Test error handling
- Validate data transformations

### 12.2 Integration Tests
- Connect to Pallex sandbox
- Test full booking flow
- Verify label generation
- Test tracking updates

### 12.3 End-to-End Tests
- Create test orders
- Select Pallex shipping
- Complete full workflow
- Verify database updates

### 12.4 Test Scenarios
1. **Standard Pallet Shipment**: Single pallet, domestic
2. **Multi-Pallet**: Multiple pallets, same destination
3. **International**: Cross-border with customs
4. **Collection Scheduling**: Future collection date
5. **Error Handling**: Invalid addresses, API failures
6. **Tracking**: Monitor shipment through delivery

---

## 13. Documentation Requirements

### 13.1 Technical Documentation
- API integration guide
- Provider class documentation
- Configuration reference
- Error code catalog
- Troubleshooting guide

### 13.2 User Documentation
- How to configure Pallex
- Creating pallet shipments
- Understanding pallet pricing
- Scheduling collections
- Tracking pallet shipments

### 13.3 Operations Documentation
- Pallex account setup
- API credential management
- Monitoring and alerts
- Support procedures
- Escalation paths

---

## 14. Sources & References

### Research Sources:
- [Pallex WMS integration Guide - Canary7 WMS](https://www.canary7.com/carrier-integrations/pallex-wms-integration/)
- [Full API Integration with CustomsPro automates Pall-Ex Customs Declarations](https://www.channelports.co.uk/case-study/full-api-ingetration-with-customspro-automates-pall-ex-customs-declarations/)
- [What is a warehouse management system?](https://www.pallex.co.uk/post/what-is-a-warehouse-management-system)
- [International Palletised Freight Network | Pall-Ex Group](https://www.pallex.com/)
- [Pallex - Documentation - Despatch Cloud](https://documentation.despatchcloud.com/books/courier-directory/page/pallex)

### Additional Resources:
- MyNexus Portal: https://mynexus.pallex.com/
- Nexus COS: https://nexus.pallex.com/
- ClientPlus API: https://clientplus.pallex.ro/api/

---

## 15. Conclusion

Implementing Pallex integration into the WMS is **technically feasible** and would be a **valuable addition** for customers requiring bulk pallet shipping. The existing shipping abstraction layer provides an excellent foundation, requiring primarily:

1. Creating a new `PallexShippingProvider` class
2. Obtaining API access and credentials from Pallex
3. Extending the database to support pallet-specific data
4. Updating the UI to support pallet shipment options

**Key Success Factors**:
- Obtaining Pallex API documentation and credentials
- Clear business requirements definition
- Phased implementation approach
- Comprehensive testing with Pallex sandbox

**Estimated Timeline**: 2-3 weeks for MVP (core functionality)

**Next Step**: Contact Pallex to discuss API access and technical requirements.

---

**Report Prepared By**: WMS Development Team
**Date**: December 3, 2025
**Version**: 1.0
