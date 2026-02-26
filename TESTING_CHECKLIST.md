# 📋 Deployment & Testing Checklist

## Pre-Deployment Checklist

### ✅ Code Review
- [x] All PHP files follow PSR-12 coding standards
- [x] All Blade templates use consistent formatting
- [x] No SQL injection vulnerabilities (using query builder)
- [x] CSRF protection enabled on form
- [x] Input validation comprehensive on server-side
- [x] Alpine.js event handlers properly scoped

### ✅ Database Preparation
- [ ] Database backup created
- [ ] Migration file reviewed
- [ ] No conflicts with existing migrations
- [ ] Foreign key constraints valid
- [ ] Indexes properly configured

### ✅ File Permissions
- [ ] App files readable by web server
- [ ] Storage directory writable
- [ ] Config files not world-readable
- [ ] .env file exists with correct values

### ✅ Environment Check
- [ ] PHP version >= 8.1
- [ ] Laravel version 11.x
- [ ] MySQL/MariaDB version >= 5.7
- [ ] All required packages installed

---

## Pre-Testing Setup

### Step 1: Database Migration
```bash
# In project root directory
php artisan migrate

# Verify the bookings table was created
php artisan tinker
>>> DB::table('bookings')->count()
// Should return 0
```

### Step 2: Clear Application Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 3: Verify Routes
```bash
php artisan route:list | grep booking

# Expected output:
# GET|HEAD   /bookings/flow/{movie}              bookings.flow
# POST       /bookings                           bookings.store
# GET|HEAD   /bookings/{booking}/confirmation   bookings.confirmation
# GET|HEAD   /bookings/{booking}                bookings.show
```

---

## Manual Testing

### Test Case 1: Step 1 - User Details Validation

**Scenario**: User cannot proceed without name and phone

**Steps**:
1. Navigate to `/bookings/flow/1` (or any valid movie ID)
2. Observe Step 1 displays with user details form
3. Click "Next" button → Should be **DISABLED** (greyed out)
4. Type name in "Full Name" field
5. Click "Next" button → Should still be **DISABLED**
6. Type phone number in "Phone Number" field
7. Click "Next" button → Should now be **ENABLED**

**Expected Result**: ✅ Button only enabled when both name and phone filled

---

### Test Case 2: Step 1 - Optional Fields

**Scenario**: Email and NIC fields are optional

**Steps**:
1. On Step 1, fill name and phone only (skip email and NIC)
2. Click "Next"
3. Observe Step 2 is displayed

**Expected Result**: ✅ Optional fields can be left blank

---

### Test Case 3: Complete Booking Flow

**Scenario**: User completes entire booking process

**Steps**:
1. Step 1: Enter name "John Doe", phone "+94712345678"
2. Optional: Enter email "john@example.com", NIC "123456789V"
3. Click "Next"
4. Step 2: Select date and time
5. Click "Next"
6. Step 3: Select one ticket type (e.g., 2 adult standard)
7. Click "Next"
8. Step 4: Select 2 seats (to match 2 tickets)
9. Click "Next"
10. Step 5: Select payment method (should default to "Card")
11. Click "Confirm Booking"

**Expected Result**: ✅ Form submits, redirects to confirmation page

---

### Test Case 4: Confirmation Page Display

**Scenario**: Confirmation page shows all booking details

**Steps**:
1. Complete booking from Test Case 3
2. On confirmation page, verify:
   - Booking reference displays (e.g., BK202602260001)
   - "Copy Reference" button works
   - Customer name displays: "John Doe"
   - Phone displays: "+94712345678"
   - Email displays: "john@example.com"
   - NIC displays: "123456789V"
   - Date shows: "Mon, Feb 24, 2026"
   - Time shows: "18:30"
   - Seats show: "A1, A2, B1" (or whatever was selected)

**Expected Result**: ✅ All information displays correctly

---

### Test Case 5: Database Record Verification

**Scenario**: Booking data is correctly stored in database

**Steps**:
1. Complete booking from Test Case 3
2. Open database client (MySQL Workbench, phpMyAdmin, or terminal)
3. Query: `SELECT * FROM bookings ORDER BY id DESC LIMIT 1;`
4. Verify fields:
   - `booking_reference`: "BK202602260001" (or similar)
   - `customer_name`: "John Doe"
   - `customer_phone`: "+94712345678"
   - `customer_email`: "john@example.com"
   - `customer_nic`: "123456789V"
   - `booking_date`: "2026-02-24"
   - `booking_time`: "18:30"
   - `selected_seats`: `["A1","A2","B1"]` (JSON array)
   - `tickets`: `{"1":{"adult":2,"child":0}}` (JSON object)
   - `payment_method`: "card"
   - `payment_status`: "pending"
   - `booked_at`: Current timestamp

**Expected Result**: ✅ All data stored correctly with proper JSON formatting

---

### Test Case 6: Back Navigation

**Scenario**: User can navigate back through steps without losing data

**Steps**:
1. Step 1: Fill name "John Doe", phone "+94712345678"
2. Click "Next"
3. Step 2: Select date and time
4. Click "Next"
5. Step 3: Select tickets
6. Click "Back"
7. Step 2: Verify date and time are still selected
8. Click "Back"
9. Step 1: Verify name and phone are still filled

**Expected Result**: ✅ All previous entries preserved when navigating back

---

### Test Case 7: Form Validation Error Handling

**Scenario**: Server validates incomplete data

**Steps**:
1. Open browser developer tools (F12)
2. Go to Network tab
3. Complete steps 1-4 of booking
4. On Step 5, modify form submission by:
   - Edit HTML to remove a required hidden field (e.g., selected_seats)
   - Submit form
5. Check Network response

**Expected Result**: ✅ Server returns 422 error with validation messages

---

### Test Case 8: Special Characters in Name

**Scenario**: Special characters in customer name are handled

**Steps**:
1. Step 1: Enter name: "O'Neill Müller"
2. Complete booking
3. Check confirmation page and database

**Expected Result**: ✅ Special characters display and store correctly

---

### Test Case 9: Phone Number Formats

**Scenario**: Different phone formats are accepted

**Steps**:
1. Test each phone format:
   - "+94712345678"
   - "071 2345678"
   - "0712-345-678"
   - "(071) 234-5678"
2. Complete booking for each

**Expected Result**: ✅ All formats accepted and stored as-is

---

### Test Case 10: Disabled Back Button on Step 1

**Scenario**: Back button is disabled on first step

**Steps**:
1. Navigate to `/bookings/flow/1`
2. Observe "Back" button

**Expected Result**: ✅ Back button is disabled (greyed out) on Step 1

---

### Test Case 11: Disabled Next Button on Step 5

**Scenario**: Next button is disabled on last step

**Steps**:
1. Complete steps 1-5
2. On Step 5, observe Next button area

**Expected Result**: ✅ Only "Confirm Booking" button visible, no Next button

---

### Test Case 12: Copy Booking Reference

**Scenario**: User can copy booking reference to clipboard

**Steps**:
1. Complete booking to reach confirmation page
2. Click "Copy Reference" button
3. Open text editor
4. Paste (Ctrl+V)

**Expected Result**: ✅ Booking reference pasted successfully

---

## Automated Testing (Optional)

### Unit Test Example
```php
// tests/Unit/BookingTest.php
public function test_booking_reference_generation()
{
    $reference = Booking::generateReference();
    $this->assertStringStartsWith('BK', $reference);
    $this->assertEquals(12, strlen($reference));
}
```

### Feature Test Example
```php
// tests/Feature/BookingFlowTest.php
public function test_can_create_booking()
{
    $response = $this->post('/bookings', [
        'movie_id' => 1,
        'customer_name' => 'John Doe',
        'customer_phone' => '+94712345678',
        'booking_date' => '2026-02-28',
        'booking_time' => '18:30',
        'selected_seats' => ['A1', 'A2'],
        'tickets' => ['1' => ['adult' => 2, 'child' => 0]],
        'total_amount' => 2500,
        'payment_method' => 'card',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('bookings', [
        'customer_name' => 'John Doe',
    ]);
}
```

---

## Performance Testing

### Load Testing Scenario
- [ ] 100 simultaneous bookings
- [ ] Average response time < 2 seconds
- [ ] Database handles concurrent inserts
- [ ] No duplicate booking references generated

### Database Query Performance
```bash
# Check query performance
php artisan tinker
>>> DB::enableQueryLog()
>>> Booking::with('movie')->get()
>>> DB::getQueryLog()
```

---

## Security Testing

### SQL Injection Test
- [x] Try entering SQL in name field: `Robert'; DROP TABLE bookings;--`
- [x] Should be safely escaped

### CSRF Test
- [x] Remove CSRF token and submit form
- [x] Should return 419 error

### XSS Test
- [x] Enter HTML in name: `<script>alert('xss')</script>`
- [x] Should be escaped in display

---

## Browser Compatibility

Test on:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Chrome
- [ ] Mobile Safari

---

## Rollback Plan

If issues occur during deployment:

### Quick Rollback
```bash
# Rollback migration
php artisan migrate:rollback --step=1

# Restore from backup
# ...restore database backup...

# Clear cache
php artisan cache:clear
```

### File Rollback
```bash
# Restore from git
git checkout HEAD -- app/Models/Booking.php
git checkout HEAD -- app/Http/Controllers/BookingController.php
# ... restore other files ...
```

---

## Post-Deployment Checks

### ✅ Application Health
- [ ] No errors in `storage/logs/laravel.log`
- [ ] All routes accessible
- [ ] Database connection working
- [ ] Sessions working properly

### ✅ User Functionality
- [ ] Can navigate booking flow
- [ ] Can complete booking
- [ ] Confirmation page displays
- [ ] Booking reference generated
- [ ] Database records created

### ✅ Error Handling
- [ ] Invalid movie ID shows error
- [ ] Invalid data shows validation errors
- [ ] Database errors handled gracefully
- [ ] 404 pages display properly

### ✅ Performance
- [ ] Page loads in < 2 seconds
- [ ] No JavaScript console errors
- [ ] No performance warnings
- [ ] Database queries optimized

---

## Monitoring Setup

### Recommended Monitoring
- [ ] Set up error tracking (e.g., Sentry)
- [ ] Enable query logging in production
- [ ] Set up database backups
- [ ] Monitor disk space
- [ ] Monitor CPU and memory usage

### Log Monitoring
```bash
# Watch Laravel logs in real-time
tail -f storage/logs/laravel.log

# Check for errors
grep -i error storage/logs/laravel.log
```

---

## Sign-Off

- [ ] Development team reviewed code
- [ ] QA completed testing
- [ ] Database backup confirmed
- [ ] Rollback plan documented
- [ ] Monitoring configured
- [ ] Ready for production deployment

**Date**: ________________
**Developer**: ________________
**QA Lead**: ________________
**Project Manager**: ________________

---

## Notes

Use this space to document any issues found and their resolutions:

```
Issue: ________________________
Resolution: ____________________
Status: [ ] Open [ ] Resolved
```

---

**Last Updated**: February 26, 2026
**Document Version**: 1.0
