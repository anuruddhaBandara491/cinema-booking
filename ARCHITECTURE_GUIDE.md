# Booking Flow Diagram & Architecture

## 1. User Journey Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      CINEMA BOOKING SYSTEM                       │
└─────────────────────────────────────────────────────────────────┘

    START: User navigates to /bookings/flow/{movieId}
              ↓
    ┌─────────────────────────────────────────┐
    │         STEP 1: YOUR DETAILS ⭐ NEW      │
    │  ─────────────────────────────────────  │
    │  [ ] Full Name (required)                │
    │  [ ] Phone Number (required)             │
    │  [ ] Email Address (optional)            │
    │  [ ] NIC/ID Number (optional)            │
    │                                         │
    │  [                Back  ] [ Next → ]     │
    │  Validation: Name & Phone must have text│
    │  Status: Can proceed? YES ✓              │
    └─────────────────────────────────────────┘
              ↓
    ┌─────────────────────────────────────────┐
    │    STEP 2: SELECT DATE & TIME           │
    │  ─────────────────────────────────────  │
    │  Date:                Time:              │
    │  [Mon,Feb24][Tue,Feb25][Wed,Feb26]     │
    │  [18:00][18:30][19:00][19:30]          │
    │                                         │
    │  [← Back] [ Next → ]                     │
    │  Validation: Both date & time selected  │
    │  Status: Can proceed? YES ✓              │
    └─────────────────────────────────────────┘
              ↓
    ┌─────────────────────────────────────────┐
    │    STEP 3: SELECT TICKETS               │
    │  ─────────────────────────────────────  │
    │  ┌──────────────┐  ┌──────────────┐    │
    │  │ STANDARD     │  │ BOX SEATS    │    │
    │  │ Adult: LKR   │  │ Adult: LKR   │    │
    │  │ [−][0][+]   │  │ [−][0][+]    │    │
    │  │ Child: LKR   │  │ Pair only    │    │
    │  │ [−][0][+]   │  └──────────────┘    │
    │  └──────────────┘                      │
    │                                         │
    │  [← Back] [ Next → ]                     │
    │  Validation: Has tickets, valid pairs   │
    │  Status: Can proceed? YES ✓              │
    └─────────────────────────────────────────┘
              ↓
    ┌─────────────────────────────────────────┐
    │    STEP 4: SELECT SEATS                 │
    │  ─────────────────────────────────────  │
    │       THEATER LAYOUT                    │
    │                                         │
    │       Entrance    Screen     Exit       │
    │        ┌──┐  ╔════════╗  ┌──┐          │
    │                                         │
    │       A [●][●][●][●][●][●][●][●]      │
    │       B [●][●][●][●][●][●][●][●]      │
    │       ...                               │
    │       F [●][●][●][●][●][●][●][●]      │
    │       G [●●][●●][●●][●●]              │
    │       H [●●][●●][●●][●●]              │
    │                                         │
    │  Selected: A1, A2, B1 (3/3 seats)      │
    │                                         │
    │  [← Back] [ Next → ]                     │
    │  Validation: Seats match ticket count   │
    │  Status: Can proceed? YES ✓              │
    └─────────────────────────────────────────┘
              ↓
    ┌─────────────────────────────────────────┐
    │    STEP 5: PAYMENT                      │
    │  ─────────────────────────────────────  │
    │  SUMMARY:                               │
    │  Date: Mon, Feb 24    Time: 18:30      │
    │  Tickets: 3           Seats: A1, A2    │
    │                                         │
    │  PAYMENT METHOD:                        │
    │  ☑ Card Payment                         │
    │  ○ Wallet                               │
    │  ○ Pay at Counter                       │
    │                                         │
    │  [ Confirm Booking ]                    │
    └─────────────────────────────────────────┘
              ↓
         [FORM SUBMIT]
              ↓
    POST /bookings (with all data)
              ↓
    ┌─────────────────────────────────────────┐
    │      BOOKING CONFIRMATION PAGE          │
    │  ─────────────────────────────────────  │
    │  ✓ Thank You!                           │
    │  Your booking has been successfully     │
    │  submitted.                             │
    │                                         │
    │  Booking Reference: BK202602260001      │
    │  Customer: John Doe                     │
    │  Phone: +94712345678                    │
    │  Movie: Avatar (18:30)                  │
    │  Seats: A1, A2, B1                      │
    │  Amount: LKR 2,500.00                   │
    │  Status: Pending                        │
    │                                         │
    │  [ Continue Shopping ] [ View Details ] │
    └─────────────────────────────────────────┘
```

---

## 2. Data Flow Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                        FRONTEND (Alpine.js)                       │
│  ────────────────────────────────────────────────────────────   │
│                                                                   │
│  userDetails = {                                                 │
│      name: '',           ←─ Step 1 Input                        │
│      phoneNumber: '',    ←─ Step 1 Input                        │
│      email: '',          ←─ Step 1 Input (Optional)             │
│      nic: ''             ←─ Step 1 Input (Optional)             │
│  }                                                               │
│                                                                   │
│  selectedDate: '...'     ←─ Step 2 Input                        │
│  selectedTime: '...'     ←─ Step 2 Input                        │
│                                                                   │
│  tickets: {              ←─ Step 3 Input                        │
│      '1': {adult: 2, child: 1},                                 │
│      '2': {adult: 0, child: 0}                                  │
│  }                                                               │
│                                                                   │
│  seats: ['A1','A2','B1'] ←─ Step 4 Input                        │
│  paymentMethod: 'card'   ←─ Step 5 Input                        │
│                                                                   │
│  canContinue() → true/false based on validation                 │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
              ↓
┌──────────────────────────────────────────────────────────────────┐
│               HTML FORM (Hidden Inputs)                          │
│  ────────────────────────────────────────────────────────────   │
│                                                                   │
│  <form action="/bookings" method="POST">                        │
│    <input name="movie_id" value="5">                            │
│    <input name="customer_name" x-model="userDetails.name">     │
│    <input name="customer_phone" x-model="userDetails.phoneNumber">
│    <input name="customer_email" x-model="userDetails.email">   │
│    <input name="customer_nic" x-model="userDetails.nic">       │
│    <input name="booking_date" x-model="selectedDate">          │
│    <input name="booking_time" x-model="selectedTime">          │
│    <input name="selected_seats" :value="JSON.stringify(seats)">│
│    <input name="tickets" :value="JSON.stringify(tickets)">     │
│    <input name="payment_method" x-model="paymentMethod">       │
│    <input name="total_amount" :value="calculateTotal()">       │
│  </form>                                                         │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
              ↓
┌──────────────────────────────────────────────────────────────────┐
│            BACKEND (BookingController@store)                     │
│  ────────────────────────────────────────────────────────────   │
│                                                                   │
│  1. Validate Input Data                                          │
│     - customer_name: required, string                           │
│     - customer_phone: required, string                          │
│     - selected_seats: required, array                           │
│     - payment_method: in ['card','wallet','cash']               │
│     ... (all 10+ fields)                                        │
│                                                                   │
│  2. Create Booking Record                                       │
│     - Generate unique reference: BK202602260001                 │
│     - Store all customer details                                │
│     - Store booking selections (as JSON)                        │
│     - Set payment_status = 'pending'                            │
│                                                                   │
│  3. Save to Database                                            │
│                                                                   │
│  4. Return Success Response                                     │
│     → Redirect to confirmation page                             │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
              ↓
┌──────────────────────────────────────────────────────────────────┐
│              DATABASE (MySQL/MariaDB)                            │
│  ────────────────────────────────────────────────────────────   │
│                                                                   │
│  bookings table:                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ id: 1                                                   │   │
│  │ movie_id: 5                                             │   │
│  │ booking_reference: BK202602260001                       │   │
│  │ customer_name: John Doe                                 │   │
│  │ customer_phone: +94712345678                            │   │
│  │ customer_email: john@example.com                        │   │
│  │ customer_nic: 123456789V                                │   │
│  │ booking_date: 2026-02-24                                │   │
│  │ booking_time: 18:30                                     │   │
│  │ selected_seats: ["A1","A2","B1"]  (JSON)              │   │
│  │ tickets: {"1":{"adult":2,"child":1},...} (JSON)       │   │
│  │ total_amount: 2500.00                                   │   │
│  │ payment_method: card                                    │   │
│  │ payment_status: pending                                 │   │
│  │ booked_at: 2026-02-26 14:30:00                         │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

---

## 3. Validation Flow

```
STEP 1 VALIDATION
┌────────────────────────────────────────┐
│ canContinue() → Step 1                 │
├────────────────────────────────────────┤
│ ✓ name.trim() !== ''                  │ (Client-side)
│ ✓ phoneNumber.trim() !== ''           │ (Client-side)
│                                        │
│ → Button enabled when BOTH true       │
└────────────────────────────────────────┘
              ↓
STEP 2 VALIDATION
┌────────────────────────────────────────┐
│ canContinue() → Step 2                 │
├────────────────────────────────────────┤
│ ✓ selectedDate !== ''                 │ (Client-side)
│ ✓ selectedTime !== ''                 │ (Client-side)
│                                        │
│ → Button enabled when BOTH true       │
└────────────────────────────────────────┘
              ↓
STEP 3 VALIDATION
┌────────────────────────────────────────┐
│ canContinue() → Step 3                 │
├────────────────────────────────────────┤
│ ✓ totalTickets() > 0                  │ (Has tickets)
│ ✓ boxTypeIds.every(isBoxValid)        │ (Pairs valid)
│                                        │
│ → Button enabled when BOTH true       │
└────────────────────────────────────────┘
              ↓
STEP 4 VALIDATION
┌────────────────────────────────────────┐
│ canContinue() → Step 4                 │
├────────────────────────────────────────┤
│ ✓ seats.length > 0                    │ (Has seats)
│ ✓ seatCount === ticketCount           │ (Matches)
│ ✓ boxSeats <= boxCapacity             │ (Box valid)
│ ✓ odcSeats <= odcTickets              │ (ODC valid)
│                                        │
│ → Button enabled when ALL true        │
└────────────────────────────────────────┘
              ↓
SERVER-SIDE VALIDATION (BookingController@store)
┌────────────────────────────────────────────────────┐
│ Validate All Input                                 │
├────────────────────────────────────────────────────┤
│ ✓ movie_id exists in movies table                 │
│ ✓ customer_name: string, max:255                  │
│ ✓ customer_phone: string, max:20                  │
│ ✓ customer_email: email, max:255 (or null)       │
│ ✓ customer_nic: string, max:50 (or null)         │
│ ✓ booking_date: valid date                        │
│ ✓ booking_time: string                            │
│ ✓ selected_seats: array, min:1                    │
│ ✓ tickets: array                                  │
│ ✓ total_amount: numeric, min:0                    │
│ ✓ payment_method: in [card,wallet,cash]          │
│                                                    │
│ If any fails → Return 422 with errors            │
│ If all pass → Create Booking record               │
└────────────────────────────────────────────────────┘
```

---

## 4. Database Schema Diagram

```
BOOKINGS TABLE
═════════════════════════════════════════════════════════════

┌──────────────────────────────────────────────────────────┐
│ IDENTIFICATION                                           │
├──────────────────────────────────────────────────────────┤
│ id ........................... BIGINT (PK)              │
│ booking_reference ............ VARCHAR (UNIQUE) [NEW]  │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│ RELATIONSHIPS                                            │
├──────────────────────────────────────────────────────────┤
│ movie_id .................... BIGINT (FK → movies.id)  │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│ CUSTOMER DETAILS [STEP 1] ⭐ NEW                        │
├──────────────────────────────────────────────────────────┤
│ customer_name ............... VARCHAR(255) NOT NULL    │
│ customer_phone .............. VARCHAR(20) NOT NULL     │
│ customer_email .............. VARCHAR(255) NULLABLE    │
│ customer_nic ................ VARCHAR(50) NULLABLE     │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│ BOOKING DETAILS [STEPS 2-4]                            │
├──────────────────────────────────────────────────────────┤
│ booking_date ................ DATE                     │
│ booking_time ................ VARCHAR                  │
│ selected_seats .............. JSON (array)             │
│   └─ Example: ["A1","A2","B1"]                        │
│ tickets ..................... JSON (object)             │
│   └─ Example: {"1":{"adult":2,"child":1},...}         │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│ PAYMENT [STEP 5]                                       │
├──────────────────────────────────────────────────────────┤
│ payment_method .............. VARCHAR [card/wallet/cash]│
│ payment_status .............. ENUM [pending/completed/ │
│                              failed/cancelled]         │
│ total_amount ................ DECIMAL(10,2)            │
│ payment_reference ........... VARCHAR NULLABLE         │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│ TIMESTAMPS                                             │
├──────────────────────────────────────────────────────────┤
│ booked_at ................... TIMESTAMP                │
│ created_at .................. TIMESTAMP                │
│ updated_at .................. TIMESTAMP                │
└──────────────────────────────────────────────────────────┘

INDEXES
───────
├─ PRIMARY: id
├─ UNIQUE: booking_reference
├─ FOREIGN: movie_id → movies(id) CASCADE
├─ REGULAR: customer_phone (for lookup by phone)
├─ REGULAR: payment_status (for payment processing)
```

---

## 5. File Structure

```
cinema_booking/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── BookingController.php ⭐ NEW
│   │
│   └── Models/
│       └── Booking.php ⭐ NEW
│
├── database/
│   └── migrations/
│       └── 2026_02_26_000000_create_bookings_table.php ⭐ NEW
│
├── resources/views/bookings/
│   ├── flow.blade.php (MODIFIED - added form, Step 1)
│   ├── confirmation.blade.php ⭐ NEW
│   └── steps/
│       ├── step-user-details.blade.php ⭐ NEW (Step 1)
│       ├── step-1.blade.php (now Step 2)
│       ├── step-2.blade.php (now Step 3)
│       ├── step-3.blade.php (now Step 4)
│       └── step-4.blade.php (now Step 5)
│
└── routes/
    └── web.php (MODIFIED - added booking routes)
```

---

## 6. Request/Response Cycle

```
CLIENT REQUEST
───────────────
POST /bookings

Headers:
  Content-Type: application/x-www-form-urlencoded
  X-CSRF-TOKEN: xxxxxxxx

Body:
{
  movie_id: 5,
  customer_name: "John Doe",
  customer_phone: "+94712345678",
  customer_email: "john@example.com",
  customer_nic: "123456789V",
  booking_date: "2026-02-24",
  booking_time: "18:30",
  selected_seats: ["A1","A2","B1"],
  tickets: {"1":{"adult":2,"child":1}},
  payment_method: "card",
  total_amount: 2500.00
}
             ↓
CONTROLLER PROCESSING
──────────────────────
BookingController@store()
1. Validate all fields
2. Check movie exists
3. Generate unique reference
4. Create Booking record
5. Set booked_at timestamp
             ↓
DATABASE INSERT
───────────────
INSERT INTO bookings (
  movie_id,
  booking_reference,
  customer_name,
  customer_phone,
  customer_email,
  customer_nic,
  booking_date,
  booking_time,
  selected_seats,
  tickets,
  total_amount,
  payment_method,
  payment_status,
  booked_at,
  created_at,
  updated_at
) VALUES (...)
             ↓
SERVER RESPONSE
───────────────
HTTP/1.1 302 Found
Location: /bookings/1/confirmation

Or if validation fails:
HTTP/1.1 422 Unprocessable Entity
{
  "errors": {
    "customer_phone": ["The phone field is required."]
  }
}
             ↓
CLIENT REDIRECT
───────────────
GET /bookings/{id}/confirmation

Display Confirmation Page with:
- Booking Reference
- Customer Details
- Booking Information
- Payment Status
```

---

## 7. State Management (Alpine.js)

```
┌─────────────────────────────────────────────────────────┐
│              ALPINE.JS STATE OBJECT                     │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  x-data="{                                             │
│    step: 1,                (Current step: 1-5)         │
│    maxStep: 5,             (Total steps)               │
│                                                         │
│    userDetails: {                                      │
│      name: '',             ← Step 1                   │
│      phoneNumber: '',      ← Step 1                   │
│      email: '',            ← Step 1                   │
│      nic: ''               ← Step 1                   │
│    },                                                  │
│                                                         │
│    selectedDate: '',       ← Step 2                   │
│    selectedTime: '',       ← Step 2                   │
│                                                         │
│    tickets: {},            ← Step 3                   │
│    boxTypeIds: [],                                    │
│                                                         │
│    seats: [],              ← Step 4                   │
│    paymentMethod: 'card',  ← Step 5                   │
│                                                         │
│    // Methods                                         │
│    canContinue() {},       ← Validation logic        │
│    calculateTotal() {},    ← Price calculation       │
│    ...                                                │
│  }"                                                    │
│                                                         │
└─────────────────────────────────────────────────────────┘

STEP NAVIGATION
───────────────

  User Clicks "Next"
        ↓
  @click="step = Math.min(maxStep, step + 1)"
        ↓
  IF canContinue() is true
        ↓
  Display next step with x-show="step === N"
        ↓
  All previous data in Alpine state preserved


  User Clicks "Back"
        ↓
  @click="step = Math.max(1, step - 1)"
        ↓
  Display previous step
        ↓
  All data in Alpine state preserved


  User Clicks "Confirm Booking" (Step 5)
        ↓
  Form submit triggered
        ↓
  All Alpine state pushed to hidden inputs
        ↓
  POST /bookings with all data
```

---

**This architecture ensures a smooth, validated booking experience with clear data flow from frontend to backend!**
