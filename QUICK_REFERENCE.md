# Quick Reference Guide - Booking System Implementation

## For Laravel Developer

### Running Migrations
```bash
php artisan migrate
```

This creates the `bookings` table with all necessary columns for storing booking information.

---

## File Locations & Responsibilities

### 1. **Models**
- **File**: `app/Models/Booking.php` ⭐ NEW
  - Handles booking data structure
  - Provides `generateReference()` static method for unique booking IDs
  - Defines relationship to Movie model

### 2. **Controllers**
- **File**: `app/Http/Controllers/BookingController.php` ⭐ NEW
  - `store()` - Validates and saves booking
  - `confirmation()` - Shows confirmation page
  - `show()` - Displays booking details

### 3. **Views**
- **Main Flow**: `resources/views/bookings/flow.blade.php` (MODIFIED)
  - Now includes form wrapper
  - Contains 5 steps total
  - Uses Alpine.js for state management

- **New User Details Step**: `resources/views/bookings/steps/step-user-details.blade.php` ⭐ NEW
  - Collects: Name (required), Phone (required), Email (optional), NIC (optional)

- **Confirmation**: `resources/views/bookings/confirmation.blade.php` ⭐ NEW
  - Displays booking confirmation with reference number
  - Shows all booking details
  - Provides copy-to-clipboard functionality

### 4. **Routes**
- **File**: `routes/web.php` (MODIFIED)
- New routes:
  ```php
  POST  /bookings                          → bookings.store
  GET   /bookings/{booking}/confirmation  → bookings.confirmation
  GET   /bookings/{booking}                → bookings.show
  ```

### 5. **Database**
- **Migration**: `database/migrations/2026_02_26_000000_create_bookings_table.php` ⭐ NEW
- Creates `bookings` table with proper indexes and relationships

---

## How the Booking Flow Works

### Step 1: User Details (NEW)
```
INPUT:
- customer_name (required)
- customer_phone (required)
- customer_email (optional)
- customer_nic (optional)

VALIDATION: Alpine.js
- Name must not be empty
- Phone must not be empty
```

### Step 2: Select Date & Time
```
INPUT:
- booking_date (selected date)
- booking_time (selected time)

VALIDATION: Both fields required
```

### Step 3: Select Tickets
```
INPUT:
- tickets[{type_id}][adult] (quantity)
- tickets[{type_id}][child] (quantity)

VALIDATION:
- At least 1 ticket
- Box tickets must be in pairs (if applicable)
```

### Step 4: Select Seats
```
INPUT:
- selected_seats[] (array of seat numbers like ['A1', 'A2', 'B3'])

VALIDATION:
- Seat count must match total ticket count
```

### Step 5: Payment
```
INPUT:
- payment_method (card, wallet, cash)
- total_amount (calculated server-side)

VALIDATION:
- Valid payment method selected
```

### Submission
```
POST /bookings
Creates Booking record with:
- Unique booking_reference (e.g., BK202602260001)
- All customer & booking details
- Redirects to confirmation page
```

---

## Key Features

### ✅ Validation
- **Client-side**: Alpine.js validates required fields before proceeding
- **Server-side**: Laravel validates all submitted data in BookingController

### ✅ Booking Reference
- Automatically generated unique format: `BK{YYYYMMDD}{4-digit-random}`
- Displayed on confirmation page
- Can be copied to clipboard

### ✅ JSON Storage
- `selected_seats`: Array of seat identifiers
- `tickets`: Object with ticket quantities by type
- Flexible for different theater configurations

### ✅ Payment Status Tracking
- Enum: `pending`, `completed`, `failed`, `cancelled`
- Allows payment processing integration

### ✅ Relationship
- Each booking linked to a Movie
- Cascade delete on movie deletion

---

## Database Schema

```sql
CREATE TABLE bookings (
    id BIGINT PRIMARY KEY,
    movie_id BIGINT NOT NULL,
    booking_reference VARCHAR(255) UNIQUE,
    
    -- Customer Details
    customer_name VARCHAR(255),
    customer_phone VARCHAR(20),
    customer_email VARCHAR(255),
    customer_nic VARCHAR(50),
    
    -- Booking Details
    booking_date DATE,
    booking_time VARCHAR(255),
    selected_seats JSON,
    tickets JSON,
    total_amount DECIMAL(10,2),
    
    -- Payment
    payment_method VARCHAR(255),
    payment_status ENUM('pending', 'completed', 'failed', 'cancelled'),
    payment_reference VARCHAR(255),
    
    -- Timestamps
    booked_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
);
```

---

## Sample Data Structure

### Booking Record (JSON in Database)
```javascript
{
    "id": 1,
    "movie_id": 5,
    "booking_reference": "BK202602260001",
    "customer_name": "John Doe",
    "customer_phone": "+94712345678",
    "customer_email": "john@example.com",
    "customer_nic": "123456789V",
    "booking_date": "2026-02-28",
    "booking_time": "18:30",
    "selected_seats": ["A1", "A2", "B1"],
    "tickets": {
        "1": {"adult": 2, "child": 1},
        "2": {"adult": 0, "child": 0}
    },
    "total_amount": 2500.00,
    "payment_method": "card",
    "payment_status": "pending",
    "booked_at": "2026-02-26 14:30:00"
}
```

---

## Extending the System

### Add Email Notifications
```php
// In BookingController::store()
Mail::send(new BookingConfirmation($booking));
```

### Add SMS Notifications
```php
// In BookingController::store()
$booking->customer_phone; // Use this
SMS::send($booking->customer_phone, "Your booking ref: {$booking->booking_reference}");
```

### Add Payment Processing
```php
// In BookingController::store() - after validation
$paymentResult = PaymentGateway::process($booking->total_amount, $booking->payment_method);
$booking->payment_reference = $paymentResult->reference;
$booking->payment_status = $paymentResult->status;
$booking->save();
```

### Add Cancellation
```php
// In BookingController
public function cancel(Booking $booking) {
    $booking->payment_status = 'cancelled';
    $booking->save();
    // Process refund if needed
}
```

---

## Testing Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Test Step 1: Enter user details
- [ ] Test Step 1 validation: Name/phone required
- [ ] Test Step 2: Select date and time
- [ ] Test Step 3: Select tickets
- [ ] Test Step 4: Select seats
- [ ] Test Step 5: Select payment method
- [ ] Submit booking: Should redirect to confirmation
- [ ] Check database: Booking record should exist
- [ ] Verify booking reference format
- [ ] Test cancellation flow
- [ ] Test edge cases (special characters in name, different phone formats)

---

## Common Issues & Solutions

### Issue: Migration fails
**Solution**: Ensure movies table exists first
```bash
php artisan migrate:status
php artisan migrate
```

### Issue: Booking form not submitting
**Solution**: Check browser console for JavaScript errors
- Verify Alpine.js is loaded
- Check form has proper CSRF token

### Issue: Hidden fields not submitting
**Solution**: Verify hidden inputs have correct `name` attributes
- Check `x-model` bindings are working
- Console log data before submit

### Issue: Booking reference not unique
**Solution**: Check `generateReference()` method
- Verify timestamp format is correct
- Check random number generation

---

## Performance Tips

1. **Add database indexes** (already in migration):
   - `movie_id`
   - `customer_phone`
   - `payment_status`
   - `booking_reference` (unique)

2. **Cache ticket types** if fetched frequently:
```php
$ticketTypes = Cache::remember('ticket-types', 3600, fn() => 
    TicketType::orderBy('name')->get()
);
```

3. **Optimize queries** in confirmation view:
```php
$booking = Booking::with('movie')->find($id);
```

---

## Security Considerations

✅ **CSRF Protection**: Form includes `@csrf`
✅ **Input Validation**: All fields validated server-side
✅ **SQL Injection**: Using Laravel's query builder
✅ **Data Integrity**: Foreign key constraints on movie_id
✅ **Sensitive Data**: Email and NIC are optional

⚠️ **TODO**: 
- Add rate limiting to prevent booking spam
- Implement user authentication for booking history
- Add encryption for NIC field if storing in production

---

## API Response Examples

### Successful Booking
```json
{
    "status": "success",
    "message": "Booking submitted successfully!",
    "booking_reference": "BK202602260001",
    "redirect_url": "/bookings/confirmation/1"
}
```

### Validation Error
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "customer_phone": ["The phone field is required."],
        "selected_seats": ["At least one seat must be selected."]
    }
}
```

---

## Development Notes

- Booking reference format is human-readable for customer support
- JSON storage allows flexibility for theater layout changes
- Payment status enum prevents invalid states
- Timestamps automatically managed by Laravel
- Soft deletes not implemented (bookings should not be deleted for audit trail)

---

**Last Updated**: February 26, 2026
**Version**: 1.0
**Status**: ✅ Implementation Complete
