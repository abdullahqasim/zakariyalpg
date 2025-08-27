# Gas Sales Module - Laravel 10 Implementation

A complete Gas Sales management system built with Laravel 10, AdminLTE 3.2, following SOLID principles and Repository pattern.

## Features

### Core Functionality
- **Sales Management**: Create, edit, confirm, and cancel gas sales
- **Dynamic Pricing**: Automatic price calculation for different cylinder sizes (6.0kg, 11.8kg, 15.0kg, 45.4kg)
- **Invoice Generation**: Professional printable invoices
- **Payment Tracking**: Record payments, refunds, and adjustments
- **Customer Ledger**: Complete transaction history with running balances
- **Status Management**: Draft → Confirmed → Partially Paid → Paid workflow

### Technical Features
- **Repository Pattern**: Clean separation of data access logic
- **Service Layer**: Business logic encapsulation
- **Form Request Validation**: Comprehensive input validation
- **AdminLTE 3.2 UI**: Modern, responsive interface
- **Database Transactions**: Data integrity assurance
- **PSR-12 Coding Standards**: Clean, maintainable code

## Database Schema

### Tables

#### `sales`
- `id` (PK)
- `user_id` (FK to users)
- `base_price_11_8` (DECIMAL) - Base price for 11.8kg cylinder
- `invoice_no` (VARCHAR) - Unique invoice number (INV-YYYYMM-XXXX)
- `status` (ENUM) - draft, confirmed, partially_paid, paid, cancelled
- `sub_total` (DECIMAL) - Sum of line totals
- `discount_amount` (DECIMAL) - Discount applied
- `grand_total` (DECIMAL) - Final amount
- `created_at`, `updated_at`

#### `sale_items`
- `id` (PK)
- `sale_id` (FK to sales)
- `size_kg` (DECIMAL) - Cylinder size
- `quantity` (INT) - Number of cylinders
- `unit_price` (DECIMAL) - Price per unit
- `line_total` (DECIMAL) - quantity × unit_price
- `created_at`, `updated_at`

#### `transactions`
- `id` (PK)
- `sale_id` (FK to sales, nullable)
- `user_id` (FK to users)
- `type` (ENUM) - sale, payment, refund, adjustment
- `amount` (DECIMAL) - Positive for debits, negative for credits
- `method` (VARCHAR) - Payment method (cash, bank, card, transfer)
- `reference` (VARCHAR) - Transaction reference
- `note` (TEXT) - Optional notes
- `created_at`, `updated_at`

## Architecture

### Repository Pattern
```
app/Repositories/
├── Interfaces/
│   ├── SaleRepositoryInterface.php
│   └── TransactionRepositoryInterface.php
└── SaleRepository.php
    └── TransactionRepository.php
```

### Service Layer
```
app/Services/
├── PricingService.php          # Price calculations
├── InvoiceNumberService.php    # Invoice number generation
├── SaleService.php            # Sale business logic
└── TransactionService.php     # Transaction management
```

### Controllers
```
app/Http/Controllers/
├── SaleController.php         # Sales CRUD operations
├── TransactionController.php  # Payment/refund management
└── LedgerController.php      # Customer ledger views
```

### Form Requests
```
app/Http/Requests/
├── SaleRequest.php           # Sale validation
└── TransactionRequest.php    # Transaction validation
```

## Installation & Setup

### 1. Prerequisites
- Laravel 10
- PHP 8.2+
- MySQL/PostgreSQL
- Composer

### 2. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed --class=GasSalesTestSeeder
```

### 3. Service Provider Registration
The `GasSalesServiceProvider` is automatically registered in `bootstrap/providers.php`.

### 4. Routes
All routes are defined in `routes/web.php` and protected by authentication middleware.

## Usage

### Creating a Sale
1. Navigate to **Gas Sales → Sales**
2. Click **New Sale**
3. Select customer and enter base price for 11.8kg cylinder
4. Add quantities for different cylinder sizes
5. System automatically calculates proportional prices
6. Apply discount if needed
7. Save sale (creates draft)

### Managing Sales
- **Draft**: Can be edited and confirmed
- **Confirmed**: Can receive payments
- **Partially Paid**: Can receive additional payments
- **Paid**: Fully paid, no further actions needed
- **Cancelled**: Sale is voided

### Recording Payments
1. From sale details page, click **Record Payment**
2. Enter payment amount and method
3. Add reference number and notes
4. Save payment (updates sale status automatically)

### Customer Ledger
1. Navigate to **Gas Sales → Customer Ledger**
2. Select customer from dropdown
3. View complete transaction history
4. See running balance and summary statistics

## API Endpoints

### Sales
- `GET /sales` - List all sales
- `GET /sales/create` - Create sale form
- `POST /sales` - Store new sale
- `GET /sales/{id}` - View sale details
- `GET /sales/{id}/edit` - Edit sale form
- `PUT /sales/{id}` - Update sale
- `GET /sales/{id}/confirm` - Confirm sale
- `GET /sales/{id}/cancel` - Cancel sale
- `GET /sales/{id}/invoice` - View invoice

### Transactions
- `GET /transactions` - List all transactions
- `GET /sales/{id}/payment` - Payment form
- `POST /sales/{id}/payment` - Record payment
- `GET /sales/{id}/pay-remaining` - Pay remaining balance
- `GET /sales/{id}/refund` - Refund form
- `POST /sales/{id}/refund` - Record refund
- `GET /sales/{id}/adjustment` - Adjustment form
- `POST /sales/{id}/adjustment` - Record adjustment

### Ledger
- `GET /ledger` - Customer ledger index
- `GET /ledger/customer/{id}` - Customer detailed ledger
- `GET /ledger/customer/{id}/summary` - Customer summary (JSON)

## Business Rules

### Pricing Calculation
```
unit_price(sizeX) = round(base_price_11_8 * (sizeX / 11.8))
```

### Status Transitions
- **Draft** → **Confirmed** (manual)
- **Confirmed** → **Partially Paid** (automatic on payment)
- **Partially Paid** → **Paid** (automatic when balance = 0)
- **Any** → **Cancelled** (manual, only for draft/confirmed)

### Balance Calculation
```
Balance = Grand Total + Sum of all transactions
```
- Positive balance = Customer owes money
- Negative balance = Customer has credit
- Zero balance = Fully paid

### Invoice Numbers
Format: `INV-YYYYMM-XXXX`
- YYYYMM: Year and month
- XXXX: Sequential number (0001, 0002, etc.)

## Testing

### Test Data
The seeder creates:
- 3 test customers
- 6-9 sales (2-3 per customer)
- Various transaction types
- Different sale statuses

### Manual Testing
1. Create a new sale
2. Add different cylinder quantities
3. Confirm the sale
4. Record partial payment
5. Record remaining payment
6. View customer ledger
7. Print invoice

## Customization

### Adding New Cylinder Sizes
1. Update `PricingService::AVAILABLE_SIZES`
2. Update validation rules in `SaleRequest`
3. Update frontend forms

### Modifying Pricing Logic
Edit `PricingService::calculateProportionalPrice()` method.

### Adding New Transaction Types
1. Update `Transaction` model constants
2. Update validation rules
3. Add new controller methods
4. Update views

### Custom Invoice Format
Modify `resources/views/sales/invoice.blade.php`.

## Security Features

- **Authentication Required**: All routes protected
- **Form Validation**: Comprehensive input validation
- **SQL Injection Protection**: Eloquent ORM
- **CSRF Protection**: Laravel built-in
- **XSS Protection**: Blade templating

## Performance Considerations

- **Database Indexes**: Added on frequently queried columns
- **Eager Loading**: Relationships loaded efficiently
- **Pagination**: Large datasets handled properly
- **Caching**: Can be added for frequently accessed data

## Troubleshooting

### Common Issues

1. **Invoice Number Generation Fails**
   - Check database connection
   - Verify `sales` table exists

2. **Price Calculation Errors**
   - Verify base price is numeric
   - Check cylinder sizes are valid

3. **Transaction Balance Issues**
   - Check transaction amounts (positive/negative)
   - Verify sale status transitions

### Debug Mode
Enable debug mode in `.env`:
```
APP_DEBUG=true
```

## Contributing

1. Follow PSR-12 coding standards
2. Write unit tests for new features
3. Update documentation
4. Use meaningful commit messages

## License

This module is part of the Laravel application and follows the same license terms.

---

**Note**: This module requires AdminLTE 3.2 assets to be properly configured in your Laravel application.
