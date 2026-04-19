# Cinema Booking System - User Details Step Implementation

## Overview
A new Step 1 (User Details) has been added to the booking flow, making it the first step before selecting date/time. All previous steps have been shifted accordingly:

- **Step 1 (NEW)**: User Details (Name, Phone, Email, NIC)
- **Step 2**: Select Date & Time (previously Step 1)
- **Step 3**: Select Tickets (previously Step 2)
- **Step 4**: Select Seats (previously Step 3)
- **Step 5**: Payment (previously Step 4)

---

## Changes Made

### 1. **Database Migration**
**File:** `database/migrations/2026_02_26_000000_create_bookings_table.php`

Created a new `bookings` table with the following structure:
- `id` - Primary key
- `movie_id` - Foreign key to movies
- `booking_reference` - Unique booking reference (e.g., BK202602260001)
- **Customer Details:**
  - `customer_name` (required)
  - `customer_phone` (required)
  - `customer_email` (optional)
  - `customer_nic` (optional)
- **Booking Details:**
  - `booking_date` - Selected date
  - `booking_time` - Selected time
  - `selected_seats` - JSON array of seat numbers
  - `tickets` - JSON object with ticket quantities
- **Payment:**
  - `payment_method` (card, wallet, cash)
  - `payment_status` (pending, completed, failed, cancelled)
  - `payment_reference` (optional)
- `total_amount` - Total booking amount
- `booked_at` - Booking timestamp
- Standard timestamps

---

### 2. **Model Creation**
**File:** `app/Models/Booking.php`

New Eloquent model with:
- Mass assignment protection for all fields
- Type casting for dates and JSON arrays
- Relationship to Movie model
- Static method `generateReference()` to create unique booking references
- Accessor methods for calculating totals

---

### 3. **Controller Creation**
**File:** `app/Http/Controllers/BookingController.php`

New controller with methods:
- `store()` - Validates and saves booking data
- `confirmation()` - Shows booking confirmation
- `show()` - Displays full booking details

Includes comprehensive validation for:
- Movie existence
- Customer details (name and phone required)
- Booking information (date, time, seats)
- Payment method

---

### 4. **View Updates - Main Flow**
**File:** `resources/views/bookings/flow.blade.php`

**Key Changes:**
- Updated `maxStep` from 4 to 5
- Added `userDetails` object to Alpine.js data with fields:
  - `name`
  - `phoneNumber`
  - `email`
  - `nic`
- Updated step progress bar to show 5 steps
- Updated step labels in header
- Updated `canContinue()` method:
  - Step 1: Validates name and phone (required)
  - Step 2-5: Updated to use correct step numbers
- Wrapped entire booking flow in a `<form>` element
- Added hidden input fields to capture and submit:
  - Movie ID
  - Customer details
  - Booking selections
  - Payment method
- Changed final "Next" button to "Confirm Booking" submit button
- Form submits to `route('bookings.store')`

---

### 5. **New Step View - User Details**
**File:** `resources/views/bookings/steps/step-user-details.blade.php`

Step 1 form with:
- **Full Name** (mandatory, required field indicator)
- **Phone Number** (mandatory, required field indicator)
- **Email Address** (optional)
- **NIC / ID Number** (optional)
- Summary panel showing entered values in real-time
- Clear field labels and helper text

**Styling:**
- Consistent with existing design system
- Responsive grid layout (2 columns on large screens)
- Interactive feedback with live preview

---

### 6. **Step File Updates**
Updated all existing step files to use correct step numbers:

| File | Old Step | New Step |
|------|----------|----------|
| `step-1.blade.php` | 1 (Date & Time) | 2 |
| `step-2.blade.php` | 2 (Tickets) | 3 |
| `step-3.blade.php` | 3 (Seats) | 4 |
| `step-4.blade.php` | 4 (Payment) | 5 |

---

### 7. **Routes Update**
**File:** `routes/web.php`

Added new routes:
```php
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/{booking}/confirmation', [BookingController::class, 'confirmation'])->name('bookings.confirmation');
Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
```

---

### 8. **Confirmation View**
**File:** `resources/views/bookings/confirmation.blade.php`

Displays booking confirmation with:
- Success message
- Unique booking reference (with copy button)
- Customer details summary
- Booking information (movie, date, time)
- Selected seats display
- Payment information and status
- Action buttons to continue shopping or view details
- Important notice about saving the booking reference

---

## Field Validation Rules

### Step 1 (User Details)
- **Name**: Required, string, max 255 characters
- **Phone**: Required, string, max 20 characters
- **Email**: Optional, valid email format, max 255 characters
- **NIC**: Optional, string, max 50 characters

### Form-Level Validation
- Movie ID must exist
- Date and time must be valid
- At least one seat must be selected
- Payment method must be one of: card, wallet, cash
- Total amount must be numeric and ≥ 0

---

## User Flow

1. **Step 1 - User Details** ✨ NEW
   - User enters: Name, Phone (required), Email & NIC (optional)
   - Real-time summary shows entered details
   - "Next" button enabled when name & phone are filled

2. **Step 2 - Select Date & Time** (Previously Step 1)
   - User selects booking date and time
   - "Next" button enabled when both are selected

3. **Step 3 - Select Tickets** (Previously Step 2)
   - User selects quantity of tickets by type
   - "Next" button enabled when tickets selected and valid

4. **Step 4 - Select Seats** (Previously Step 3)
   - User selects individual seats
   - "Next" button enabled when seats match ticket count

5. **Step 5 - Payment** (Previously Step 4)
   - User selects payment method (Card, Wallet, Counter)
   - Summary shows all booking details
   - "Confirm Booking" button submits the form

6. **Confirmation Page** ✨ NEW
   - Shows booking reference number
   - Displays all booking details
   - Shows payment status
   - Provides next steps

---

## Alpine.js Features

The booking flow uses Alpine.js for client-side state management:

```javascript
userDetails: {
    name: '',          // Full name
    phoneNumber: '',   // Phone number
    email: '',         // Email address
    nic: ''            // NIC/ID number
}
```

Real-time validation:
- Name and phone must be non-empty to proceed from Step 1
- Form data is automatically sent to hidden inputs
- Total calculation (placeholder on client, actual calculation server-side)

---

## Backend Processing

### BookingController::store()
1. Validates all input data
2. Creates a new Booking record
3. Generates unique booking reference
4. Stores customer details and selections
5. Redirects to confirmation page with success message

### Booking Model
- Automatically casts arrays for seats and tickets
- Provides helper methods for data calculation
- Maintains relationship to Movie model

---

## Database Queries

After running migrations, execute:
```bash
php artisan migrate
```

This will create the `bookings` table with all necessary columns and indexes.

---

## Testing the Implementation

1. **Visit the booking page:** Navigate to `/bookings/flow/{movieId}`
2. **Step 1 Test:**
   - Try clicking "Next" without filling name/phone → Should be disabled
   - Fill name and phone → "Next" should be enabled
   - Fill optional fields (email, NIC) → Summary updates in real-time

3. **Complete Booking:**
   - Fill all steps correctly
   - Click "Confirm Booking" on Step 5
   - Should redirect to confirmation page
   - Booking reference should be displayed

4. **Database Check:**
   - Check `bookings` table for new record
   - Verify customer details are stored correctly
   - Verify selected seats and tickets are in JSON format

---

## Future Enhancements

1. **Email Notifications:** Send confirmation email if provided
2. **SMS Notifications:** Send confirmation SMS to phone number
3. **Booking Management:** Allow users to view and manage their bookings
4. **Payment Integration:** Connect to actual payment gateways for card/wallet payments
5. **Seat Availability:** Check real-time seat availability
6. **QR Code:** Generate QR code for ticket validation at cinema
7. **Cancellation:** Allow users to cancel bookings with refund options

---

## Technical Stack

- **Framework**: Laravel 11
- **Frontend**: Alpine.js, Tailwind CSS
- **Database**: MySQL/MariaDB
- **Form Submission**: POST to `/bookings` endpoint
- **Session Management**: Laravel sessions for CSRF protection

---

## File Structure Summary

```
app/
├── Http/Controllers/
│   └── BookingController.php (NEW)
└── Models/
    └── Booking.php (NEW)

database/migrations/
└── 2026_02_26_000000_create_bookings_table.php (NEW)

resources/views/bookings/
├── flow.blade.php (UPDATED)
├── confirmation.blade.php (NEW)
└── steps/
    ├── step-user-details.blade.php (NEW)
    ├── step-1.blade.php (UPDATED - now Step 2)
    ├── step-2.blade.php (UPDATED - now Step 3)
    ├── step-3.blade.php (UPDATED - now Step 4)
    └── step-4.blade.php (UPDATED - now Step 5)

routes/
└── web.php (UPDATED)
```

---

## Implementation Complete! 🎉

The booking flow now includes a dedicated Step 1 for collecting user details before proceeding with date, ticket, and seat selection. All validation and data handling is in place, with a confirmation page showing the booking reference and details.
