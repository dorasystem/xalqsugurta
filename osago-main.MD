# OSAGO Main.blade.php - Technical Analysis & Review

**File:** `resources/views/pages/insurence/osago/main.blade.php`
**Size:** 880 lines, ~275KB
**Date:** 2025-11-24
**Reviewer:** Senior Insurance Product Manager & JavaScript Engineer

---

## 1. Overview

### Purpose
This Blade view file implements the main user interface for collecting OSAGO (Obligatory Auto-insurance) data in Uzbekistan. It handles the complete insurance application workflow through a multi-step form process.

### User Flow (Intended Steps)
1. **Step 1: Vehicle Information** - User enters government number, tech passport series/number → API lookup → Display vehicle details
2. **Step 2: Owner Information** - User enters passport series/number and PINFL → API lookup → Display owner details
3. **Step 3: Applicant Information** - User either checks "applicant is owner" OR manually searches for applicant details
4. **Step 4: Policy Calculation** - System calculates insurance premium based on vehicle type, region, period, and driver limitations
5. **Step 5: Driver Information (Optional)** - If "limited drivers" is selected, user can add up to 5 drivers
6. **Step 6: Confirmation** - Final review before submission

### Technology Stack
- **Laravel Blade** templating with custom components
- **Vanilla JavaScript** (no frameworks) for client-side logic
- **Fetch API** for AJAX requests
- **Bootstrap 5** for styling (inferred from classes)
- **External APIs** for vehicle, person, and driver data validation

---

## 2. Front-end Architecture and Code Structure

### Current Organization

#### HTML/Blade Structure
```
main.blade.php (880 lines)
├── Blade layout/sections (lines 1-11)
├── Form container (lines 20-68)
│   ├── Error display block (lines 30-56)
│   └── Custom Blade components (lines 58-66):
│       ├── x-insurence.vehicle-info
│       ├── x-insurence.owner-info
│       ├── x-insurence.applicant-info
│       ├── x-insurence.policy-calculation
│       ├── x-insurence.limited-drivers-info
│       └── x-insurence.confirmation
├── Inline JavaScript (lines 72-877) - 805 lines!
└── Custom component: x-insurence.calculate (sidebar)
```

#### JavaScript Organization (Inline - Lines 72-877)

**Globals & Configuration:**
- `CONFIG` object (lines 97-126): Insurance calculation constants, vehicle types, date mappings
- `state` object (lines 129-135): Minimal state tracking (regionId, period, vehicleType, limitedC, driverIdCounter)
- `elements` object (lines 138-150): Cached DOM element references

**Event Listeners:**
- Lines 153-154: Date change listeners
- Lines 255-294: Vehicle search handler
- Lines 345-396: Owner info search handler
- Lines 486-488: Applicant checkbox listener
- Lines 492-533: Applicant search handler
- Lines 619-624: Driver limit radio button handlers
- Lines 649-777: Driver search and add handler
- Lines 863-875: Global click handler for driver deletion

**Business Logic Functions:**
- `populateVehicleInfo()` (298-341): Parse and display vehicle data
- `populateOwnerInfo()` (399-422): Parse and display owner data
- `populateApplicantInfo()` (536-553): Parse and display applicant data
- `checkApplicantInfo()` (425-483): Handle "applicant is owner" checkbox
- `calculatePolicy()` (556-589): Calculate insurance premium
- `updateEndDate()` (592-616): Calculate policy end date based on period
- `showDriverAddButton()` (626-645): Toggle limited/unlimited driver UI

**Utility Functions:**
- `sendPostRequest()` (781-801): Generic AJAX POST wrapper
- `getNecessaryInfo()` (803-816): Extract required fields from API response
- `validateInputs()` (818-820): Basic empty check validation
- `setButtonLoading()` (822-829): Toggle loading state on buttons
- `showSection()/hideSection()` (831-839): Show/hide DOM elements
- `scrollToElement()` (841-849): Smooth scroll to element
- `updateElement()` (851-854): Update innerHTML
- `formatCurrency()` (856-860): Format numbers as currency

### Critical Issues with Current Structure

#### ❌ **Massive Inline Script Block**
- **805 lines of JavaScript** embedded in a Blade template
- Makes testing impossible (cannot unit test)
- No code reusability
- Difficult to debug and maintain
- No separation between presentation and logic

#### ❌ **Tightly Coupled to DOM**
- Magic strings everywhere: `'gov_number'`, `'insurance-passport-series'`, etc.
- Direct DOM manipulation throughout
- No abstraction layer between data and view
- Changing an HTML ID breaks JavaScript

#### ❌ **No Clear Component Boundaries**
- The file uses Blade components (`x-insurence.*`) but JavaScript directly manipulates their internals
- Components are not self-contained
- Unclear responsibilities between main file and components

#### ❌ **Hidden State in DOM**
Critical form state is stored as JSON strings in hidden inputs:
- `owner-infos` (line 414-416): Complete owner data as JSON
- `applicant-infos` (line 420, 466, 480, 551): Complete applicant data as JSON
- `other_info` (line 337-340): Vehicle technical data as JSON
- `insurance-infos` (line 578-584): Calculation results as JSON

**Problem:** This creates a "dual source of truth" - both visible inputs AND hidden JSON fields must stay synchronized.

#### ❌ **Poor State Management**
The `state` object (lines 129-135) only tracks:
```javascript
{
    regionId: null,
    period: null,
    vehicleType: null,
    limitedC: 3,
    driverIdCounter: 0
}
```

**Missing from state:**
- Vehicle information (stored only in DOM inputs)
- Owner information (stored in hidden JSON + DOM inputs)
- Applicant information (stored in hidden JSON + DOM inputs)
- Drivers list (stored only in dynamically generated DOM)
- Form validation status
- Current step/section visibility
- Edit mode flags

---

## 3. UX and UI Problems (Uzbekistan-Specific)

### Critical UX Flow Issues

#### ❌ **No Clear Edit Mechanism**

**Problem 1: Readonly Inputs After Search (Line 299-301)**
```javascript
[govNumber, techPassportSeries, techPassportNumber].forEach(el =>
    el?.setAttribute('readonly', true)
);
```
After vehicle search succeeds, the inputs become readonly. There is **no visible Edit button** or mechanism to unlock these fields. User is stuck if they made a typo.

**Problem 2: No Edit Flow for Completed Steps**
- After filling Owner info → moving to Applicant → User cannot easily go back to edit Owner
- No visual indication that previous steps can be edited
- No "Edit" buttons on display sections
- Unclear whether editing an earlier step will reset later steps

#### ❌ **Confusing "Applicant is Owner" Checkbox Logic**

**Lines 424-483: `checkApplicantInfo()` function**

**Issues:**
1. When checked: Copies owner data to applicant fields, shows applicant-info-display, shows policy calculation
2. When unchecked: Clears applicant fields, hides applicant display, hides policy calculation
3. **BUT:** If user had already manually searched for applicant info, unchecking loses all that data with no warning
4. No visual feedback about what happens when toggling
5. The function shows `applicant-info-search` section in both cases (lines 459, 475) - redundant logic

**Scenario Missing:**
- User searches applicant manually → fills applicant → later decides "applicant is owner" → checkbox overrides manual entry with no confirmation

#### ❌ **Step Navigation is Implicit**

**Lines 220-251: Section visibility based on `old()` values**
```php
@if (old('model'))
    // Show vehicle display and owner input
@endif
@if (old('owner.lastName'))
    // Show owner display and applicant input
@endif
```

**Problems:**
- Step progression is controlled by showing/hiding sections (`d-none` class)
- No explicit "Next" or "Back" buttons between steps
- User must click specific search buttons to progress
- No breadcrumb or progress indicator showing current step
- No validation before moving to next step
- Unclear what fields are required before "next step" appears

#### ❌ **Uzbekistan-Specific Validation Issues**

**Missing Input Masks/Formats:**

1. **Passport Series/Number:**
   - Uzbekistan passports: `AA1234567` (2 letters + 7 digits) or older `AC1234567` format
   - No validation for correct format
   - No auto-uppercase for series letters
   - No length restriction

2. **PINFL (Personal Identification Number):**
   - Should be exactly 14 digits
   - No mask like `__ ___ __ __ __ __` for readability
   - No validation visible

3. **Government Vehicle Number:**
   - Uzbekistan format: `01 A 123 BC` (region + series + number + series)
   - No formatting mask
   - No auto-uppercase
   - No regional prefix validation

4. **Tech Passport Series/Number:**
   - Format varies but should be validated
   - No uppercase enforcement

5. **Date Fields:**
   - Uses ISO format `YYYY-MM-DD` (HTML5 date input)
   - Uzbekistan users typically expect `DD.MM.YYYY` format
   - No localized date picker

6. **Phone Number:**
   - Not visible in this file (likely in components)
   - Should validate Uzbekistan format: `+998 XX XXX XX XX`

#### ❌ **Error Messages and Hints**

**Generic Error Messages (Lines 262-263, 353-354, etc.):**
```javascript
showSimpleToast('error', '{{ __('messages.error') }}',
    '{{ __('errors.all_fields') }}');
```

**Issues:**
- Generic "all fields required" message doesn't specify WHICH field is missing
- No inline validation feedback (field turns red only after form submit)
- No helpful hints about expected formats (e.g., "Passport series: 2 uppercase letters")
- API error messages are generic (lines 286-287): "Connection with API" - doesn't explain what went wrong

#### ❌ **Visual Feedback Issues**

**Loading States:**
- Button shows spinner during API call (lines 822-829) - **GOOD**
- But no indication that other parts of form are locked during API call
- No skeleton loaders for data being fetched

**Success States:**
- No visual confirmation when data loads successfully
- Sections just appear (via `showSection()`)
- User might not notice new section appeared below

**Field States:**
- No visual difference between "not yet filled" vs "readonly because locked" vs "disabled"
- Readonly fields look identical to editable fields

---

## 4. JavaScript Logic and State Management Problems

### Critical Bug: Inconsistent State When Editing

#### 🐛 **Scenario 1: Edit Vehicle After Owner is Filled**

**Steps:**
1. User searches vehicle → Vehicle data populated → `owner-info` section shown
2. User searches owner → Owner data populated → Hidden input `owner-infos` contains JSON
3. User realizes vehicle gov number was wrong
4. User wants to edit vehicle info → **Fields are readonly (line 300)**
5. User cannot edit, no option to unlock

**What SHOULD happen:**
- Edit button to unlock vehicle fields
- Warning: "Editing vehicle will clear owner and applicant data"
- Clear downstream data (owner, applicant, drivers)
- Reset form to step 1

**What DOES happen:**
- User is stuck, must refresh page and start over

#### 🐛 **Scenario 2: Edit Owner After Applicant is Filled**

**Steps:**
1. Vehicle → Owner → Applicant (manually searched) → Policy calculation shown
2. User notices owner passport number was wrong
3. User wants to edit owner info → **Owner passport fields likely readonly after search**
4. Even if user could edit:
   - `owner-infos` hidden input (line 414-416) still contains old owner JSON
   - `applicant-infos` hidden input might be out of sync
   - If "applicant is owner" was checked, applicant data wouldn't update

**What SHOULD happen:**
- Edit button to unlock owner fields
- Re-search owner info
- Update `owner-infos` hidden input
- If "applicant is owner" checked → also update `applicant-infos`
- Recalculate policy
- Revalidate form

**What DOES happen:**
- Inconsistent state, hidden JSON doesn't match visible inputs

#### 🐛 **Scenario 3: Toggle "Applicant is Owner" Multiple Times**

**Steps:**
1. Owner info filled
2. Check "applicant is owner" → Copies owner to applicant (line 432-456)
3. User manually edits some applicant fields (e.g., address)
4. Uncheck "applicant is owner" → **Clears applicant fields (line 470-473), loses manual edits**
5. Check again → Copies owner data again, previous manual edits lost

**Problem:**
- No warning about data loss
- No way to recover manual edits
- User expects checkbox to just "use owner data" but it overwrites everything

#### 🐛 **Scenario 4: Change Insurance Period After Drivers Added**

**Steps:**
1. User selects "unlimited drivers" (line 635-643)
   - Period select becomes disabled, forced to 12 months (line 639-640)
   - `state.limitedC = 3`
2. User adds drivers... wait, they selected UNLIMITED, why add drivers?
3. User changes to "limited drivers" (line 630-634)
   - Period select enabled again
   - `state.limitedC = 1`
   - `calculatePolicy()` runs (line 644)
4. User changes period from 12 months → 6 months
5. Policy recalculated, but **driver list UI doesn't show "only valid for 6 months" warning**

**Problem:**
- No validation that limited drivers must be listed
- No sync between driver count and period restrictions
- calculatePolicy() doesn't validate driver requirements

### Data Synchronization Issues

#### ❌ **Hidden JSON Inputs vs. Visible Form Fields**

**The Problem:**
Three layers of data storage:
1. **Visible form inputs:** User sees and can sometimes edit
2. **Hidden JSON inputs:** (`owner-infos`, `applicant-infos`, etc.) - submitted to server
3. **State object:** Minimal, only calculation variables

**Lines with JSON Storage:**
- Line 337-340: `other_info` = Vehicle tech details
- Line 414-416: `owner-infos` = Owner personal details
- Line 420: `applicant-infos` = Copy of owner (if checkbox)
- Line 466: `applicant-infos` = Synced with owner when checkbox ON
- Line 480: `applicant-infos` = Cleared when checkbox OFF
- Line 551: `applicant-infos` = Applicant data from manual search
- Line 578-584: `insurance-infos` = Calculation results

**Synchronization Bugs:**

1. **Line 420 vs 466:**
   ```javascript
   // Line 420 - after owner search
   if (elements.applicantInfos) {
       elements.applicantInfos.value = JSON.stringify(ownerInfo);
   }

   // Line 466 - when checkbox is checked
   if (elements.applicantInfos && ownerInfosEl && ownerInfosEl.value) {
       elements.applicantInfos.value = ownerInfosEl.value;
   }
   ```
   First sets from `ownerInfo` object, second sets from `ownerInfosEl.value` string. These might differ!

2. **Visible inputs copied, but hidden JSON copied separately:**
   ```javascript
   // Lines 432-456: Copies individual visible fields
   ownerFields.forEach(field => {
       applicantEl.value = ownerEl.value;
   });
   // Line 466: Copies JSON separately
   elements.applicantInfos.value = ownerInfosEl.value;
   ```
   If owner visible inputs were manually edited (somehow), JSON won't match.

#### ❌ **State Object Doesn't Track Form Progress**

**Current state (lines 129-135):**
```javascript
const state = {
    regionId: null,           // Derived from gov_number
    period: null,             // From period select
    vehicleType: null,        // From API response
    limitedC: 3,              // Driver limitation coefficient (3=unlimited, 1=limited)
    driverIdCounter: 0        // For unique driver IDs
};
```

**Missing from state:**
- `vehicleDataLoaded: false` - Track if vehicle search completed
- `ownerDataLoaded: false` - Track if owner search completed
- `applicantDataLoaded: false` - Track if applicant search completed
- `isApplicantOwner: false` - Track checkbox state
- `driversList: []` - Track added drivers
- `currentStep: 1` - Track which step user is on
- `editMode: null` - Track if user is editing a previous step
- `formValid: false` - Track overall validation state

**Impact:** Cannot reliably determine form state, cannot prevent invalid transitions, cannot restore state correctly.

### Event Handling Issues

#### ❌ **Multiple Event Listeners on Same Elements**

**Line 153-154:**
```javascript
if (elements.startInput) elements.startInput.addEventListener('change', updateEndDate);
if (elements.periodSelect) elements.periodSelect.addEventListener('change', updateEndDate);
```

**Line 247-250:** (inside `@if(old('gov_number'))` block)
```javascript
setTimeout(() => {
    updateEndDate();
    calculatePolicy();
}, 100);
```

**Problem:**
- If page loads with old values, `updateEndDate()` runs automatically via setTimeout
- Then if user changes date/period, listener triggers `updateEndDate()` again
- But if user navigates away and back, listeners might be attached multiple times (if Turbo/Livewire/SPA routing used)
- No cleanup/removal of old listeners

#### ❌ **Delegated Click Handler Risks (Line 863-875)**

```javascript
document.addEventListener('click', function(e) {
    const button = e.target.closest('.btn-danger[data-target]');
    if (button) {
        const targetId = button.getAttribute('data-target');
        if (confirm('Haydovchini o\'chirmoqchimisiz?')) {
            const card = document.querySelector(`.card-footer[data-id="${targetId}"]`);
            if (card) {
                card.remove();
                calculatePolicy();
            }
        }
    }
});
```

**Issues:**
- Uses `confirm()` with hardcoded Uzbek text - not translatable, poor UX
- Global click listener on `document` - potential performance issue if many click events
- Relies on specific class `.btn-danger[data-target]` - fragile, might match unintended buttons elsewhere on page
- After deletion, calls `calculatePolicy()` but doesn't update any driver count UI or validation

### Async/Await Error Handling Issues

#### ⚠️ **Inconsistent Error Handling**

**Example 1 (Lines 275-293):**
```javascript
try {
    const result = await sendPostRequest('/get-vehicle-info', data);
    if (result?.data?.result) {
        populateVehicleInfo(result.data.result, ...);
        showSection('vehicle-info-display');
        showSection('owner-info');
        scrollToElement('vehicle-info-display');
    } else {
        showSimpleToast('error', 'XALQ SUG\'URTA', '{{ __('errors.connection_with_api') }}');
    }
} catch (error) {
    showSimpleToast('error', 'XALQ SUG\'URTA', '{{ __('errors.unexpected_issue') }}: ' + error.message);
} finally {
    setButtonLoading(elements.searchBtn, false);
}
```

**Issues:**
1. **`result?.data?.result`** - Too much optional chaining, unclear what structure is expected
2. If API returns success but wrong data structure → Generic error "connection_with_api"
3. If API returns specific error message → Shown as "unexpected_issue: [message]"
4. No logging of errors for debugging
5. No retry mechanism for network failures
6. Error displayed in toast (likely auto-dismisses) - user might miss it

**Example 2 (Lines 684-776 - Driver search):**
```javascript
try {
    const result = await sendPostRequest('/get-driver-info', driverData);
    if (result?.success && result?.data?.result) {
        const shortResult = result.data.result;
        const personResult = await sendPostRequest('/get-person-info', personData);
        if (!personResult?.data?.result) {
            throw new Error('Haydovchi ma\'lumotlari topilmadi');
        }
        // ... build driver HTML ...
        driverInfoDisplay.innerHTML += driverHtml;
```

**Issues:**
1. **Two sequential API calls** inside try block - if second fails, first succeeded but data not used
2. **`innerHTML +=`** (line 760) - Dangerous! Repeatedly parsing HTML, could cause XSS if data contains malicious HTML (though unlikely from API)
3. Hard-coded error message `'Haydovchi ma\'lumotlari topilmadi'` - not localized
4. Driver fields cleared (line 764-766) even if addition failed - confusing UX

### Form Submission Validation (Lines 156-217)

#### ⚠️ **Client-Side Only Validation**

```javascript
formEl.addEventListener('submit', function(e) {
    const ownerInfosEl = document.getElementById('owner-infos');
    const applicantInfosEl = document.getElementById('applicant-infos');
    const otherInfoEl = document.getElementById('other_info');

    function parseJsonSafe(value) {
        if (!value) return null;
        try {
            return JSON.parse(value);
        } catch (_) {
            return null;
        }
    }

    // Validate owner-infos contains required keys
    const ownerInfos = parseJsonSafe(ownerInfosEl?.value);
    const ownerMissing = missingKeys(ownerInfos, requiredOwnerKeys);
    if (ownerMissing.length) {
        e.preventDefault();
        showSimpleToast('error', 'XALQ SUG\'URTA', '{{ __('messages.validation_errors_found') }}');
        ownerInfosEl.classList.add('is-invalid');
        return;
    }
    // ... more validation
});
```

**Issues:**
1. **Validation happens only on submit** - should validate earlier (on blur, on step change)
2. **No field-level feedback** - just generic toast error
3. **Only validates JSON hidden inputs** - doesn't validate visible form fields
4. **Easy to bypass** - User can edit hidden input values via browser DevTools
5. **No server-side validation shown** - Backend must re-validate everything (hopefully it does)

---

## 5. Data Formatting & Validation Issues

### Uppercase/Serial Number Fields

#### ❌ **No Auto-Uppercase Implementation**

**Fields that should be uppercase (Uzbekistan standards):**
1. **Passport Series:** `insurance-passport-series`, `applicant-passport-series`, `driver-passport-series`
2. **Tech Passport Series:** `tech_passport_series`
3. **Government Number:** `gov_number` (at least the letter parts)
4. **Driver License Series:** `licenseSeria` (stored but not shown in input)

**Current Implementation:**
- **NONE** - No auto-uppercase, no validation, no input transformation
- User can type lowercase, gets sent to API, API might reject or auto-convert

**Recommended Solutions:**

**Option A: Auto-uppercase on input**
```javascript
// Add to each serial field
element.addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});
```

**Option B: CSS text-transform (display only)**
```css
.serial-input {
    text-transform: uppercase;
}
```
**Problem:** This only changes display, not the actual value sent to server.

**Option C: Input mask library (best)**
Use a library like `inputmask` or `cleave.js`:
```javascript
// For passport series (2 letters)
new Cleave('#passport-series', {
    blocks: [2],
    uppercase: true,
    numericOnly: false
});
```

### Input Masks Missing

#### ❌ **No Input Masks for Uzbekistan-Specific Formats**

**1. Passport Series + Number: `AA1234567`**
```
Current: <input id="insurance-passport-series"> + <input id="insurance-passport-number">
Problem: No length limit, no format enforcement, separate fields
Better: Single field with mask: AA-1234567
```

**2. PINFL: 14 digits**
```
Current: <input id="insurance-pinfl">
Problem: No mask, hard to read (e.g., 12345678901234)
Better: Mask with spaces: 12 345 67 89 01 23 4
```

**3. Government Vehicle Number: `01 A 123 BC`**
```
Current: <input id="gov_number">
Problem: No format guidance, users might type "01A123BC" or "01-A-123-BC"
Better: Auto-format with spaces, validate region code (01-99)
```

**4. Tech Passport Series + Number**
```
Current: Separate inputs
Better: Combined with format validation
```

**5. Phone Number (not visible in this file)**
```
Expected Uzbekistan format: +998 XX XXX XX XX
Should have: Country code locked, auto-spacing
```

### Date Handling Issues

#### ⚠️ **ISO Format vs. User Expectations**

**Lines 595-613: `updateEndDate()` function**
```javascript
if (!elements.startInput.value) {
    const today = new Date();
    elements.startInput.value = today.toISOString().split('T')[0]; // YYYY-MM-DD
    startDate = today;
} else {
    startDate = new Date(elements.startInput.value);
}

const endDate = new Date(startDate);
endDate.setMonth(endDate.getMonth() + monthsToAdd);
endDate.setDate(endDate.getDate() - 1);

elements.endInput.value = endDate.toISOString().split('T')[0]; // YYYY-MM-DD
```

**Issues:**
1. **HTML5 Date Input** (`type="date"`) - Format is always `YYYY-MM-DD` internally
2. **Display format** - Depends on browser locale:
   - Uzbekistan users likely have browsers set to Russian or Uzbek locale
   - Might display as `DD.MM.YYYY` to user, but value is `YYYY-MM-DD`
   - **Confusion:** User sees one format, backend receives another
3. **Timezone Issues:**
   - `new Date()` uses local timezone
   - `.toISOString()` converts to UTC
   - If user near midnight, date might shift
4. **End Date Calculation:**
   - `endDate.setMonth(endDate.getMonth() + monthsToAdd)` - Good for most months
   - `endDate.setDate(endDate.getDate() - 1)` - Subtracts 1 day
   - **Edge case:** If start date is Jan 31, adding 1 month gives Mar 3 (Feb has 28 days), then subtracting 1 day gives Mar 2 - **WRONG**

**Better Date Calculation:**
```javascript
// End date should be: (start + N months - 1 day)
const endDate = new Date(startDate);
endDate.setMonth(endDate.getMonth() + monthsToAdd);
endDate.setDate(0); // Sets to last day of previous month
```

### VIN/Engine Number Validation

#### ❌ **No Validation for Critical Vehicle Identifiers**

**Line 305:** `'engine_number': result.engineNumber || ''`

**Issues:**
1. No validation that engine number is in correct format
2. No VIN validation (VIN should be 17 characters, alphanumeric, no I/O/Q)
3. No check digit validation for VIN
4. User cannot manually edit these fields if API returns wrong data

### Currency Formatting

#### ✅ **Currency Format is OK, but...**

**Lines 856-860:**
```javascript
function formatCurrency(amount) {
    return amount.toLocaleString('en-US', {
        minimumFractionDigits: 2
    });
}
```

**Issues:**
1. Uses `'en-US'` locale - Formats as `168,000.00` (comma thousands, period decimal)
2. **Uzbekistan standard:** Usually uses spaces for thousands: `168 000.00` or `168 000,00`
3. **Currency symbol:** Should display UZS or сум (Uzbek sum symbol)

**Better:**
```javascript
function formatCurrency(amount) {
    return amount.toLocaleString('uz-UZ', {
        style: 'currency',
        currency: 'UZS',
        minimumFractionDigits: 0 // Uzbek sum usually doesn't use decimals
    });
}
```
Output: `168 000 сум`

---

## 6. Security and Reliability Concerns

### Security Issues

#### 🔴 **Critical: Client-Side Validation Only**

**Lines 156-217:** Submit event listener validates JSON fields
```javascript
formEl.addEventListener('submit', function(e) {
    // Validates owner-infos, applicant-infos, other_info
    // Prevents submit if invalid
});
```

**Problem:**
- All validation is in browser JavaScript
- Malicious user can:
  1. Open browser DevTools
  2. Remove event listener: `formEl.replaceWith(formEl.cloneNode(true))`
  3. Edit hidden JSON inputs directly
  4. Submit invalid/malicious data
- **Backend MUST re-validate everything** - hope it does, but code not shown here

#### 🔴 **Critical: Potential XSS via innerHTML**

**Line 760:**
```javascript
driverInfoDisplay.innerHTML += driverHtml;
```

**Variables in `driverHtml` (lines 725-757):**
- `${uniqueId}` - Safe (internal counter)
- `${fullName}` - **Dangerous!** - From API: `result.DriverInfo.pOwner.split(' ')`
- `${driverInfoJson}` - **Dangerous!** - JSON stringified but used in HTML attribute

**Line 717-718:**
```javascript
const driverInfoJson = JSON.stringify(driverSelfInfo)
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
```

**Partial Mitigation:** Escapes quotes, but still risky:
- If API returns name like: `John<script>alert(1)</script>`, it gets inserted into HTML
- If API is compromised or returns malicious data, XSS possible

**Better Solution:**
```javascript
// Use createElement instead of innerHTML
const cardFooter = document.createElement('div');
cardFooter.className = 'card-footer mb-3';
cardFooter.setAttribute('data-id', uniqueId);

const nameInput = document.createElement('input');
nameInput.type = 'text';
nameInput.value = fullName; // Safe: value is escaped automatically
nameInput.readonly = true;
```

#### ⚠️ **No CSRF Protection in AJAX Requests**

**Lines 782-792: `sendPostRequest()` function**
```javascript
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const response = await fetch(url, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
    },
    body: JSON.stringify(data)
});
```

**Looks Good, BUT:**
- If `csrfToken` is null (meta tag missing), request sends without CSRF token
- No fallback or warning
- Should fail loudly: `if (!csrfToken) throw new Error('CSRF token missing');`

#### ⚠️ **No Input Sanitization Before API Calls**

**Example (Lines 267-271):**
```javascript
const data = {
    gov_number: govNumber.value.trim(),
    tech_passport_series: techPassportSeries.value.trim(),
    tech_passport_number: techPassportNumber.value.trim()
};
```

**Only `.trim()` is applied - no:**
- Length validation (e.g., gov_number should be specific length)
- Character validation (e.g., passport series should be letters only)
- SQL injection prevention (backend's job, but frontend should also validate)

### Reliability Issues

#### ⚠️ **Race Conditions Possible**

**Scenario:**
1. User clicks "Search Owner" button (line 345-396)
2. API call starts
3. User immediately clicks "Search Owner" again (button disabled state might not update fast enough)
4. Two API calls in flight
5. Responses arrive out of order
6. Second response overwrites first response
7. **Wrong data displayed**

**Current Mitigation:** `setButtonLoading(elements.ownerInfoBtn, true)` disables button
**Problem:** If disabled state doesn't apply before second click (rare but possible), race condition occurs

**Better Solution:**
```javascript
let ownerSearchInProgress = false;

elements.ownerInfoBtn.addEventListener('click', async function() {
    if (ownerSearchInProgress) return; // Guard against double-click
    ownerSearchInProgress = true;

    try {
        // ... API call ...
    } finally {
        ownerSearchInProgress = false;
    }
});
```

#### ⚠️ **No Network Error Retry Logic**

**Lines 794-798:**
```javascript
const result = await response.json();

if (!response.ok) {
    throw new Error(result.message || 'So\'rov muvaffaqiyatsiz tugadi');
}
```

**Problem:**
- If network fails (timeout, DNS error, etc.) → User sees generic error
- No automatic retry
- User must click search button again
- In poor network conditions (common in Uzbekistan outside Tashkent), frustrating UX

**Better:**
```javascript
async function sendPostRequest(url, data, retries = 3) {
    for (let i = 0; i < retries; i++) {
        try {
            const response = await fetch(url, {/*...*/});
            if (response.ok) {
                return await response.json();
            }
            if (i === retries - 1) throw new Error('Request failed after ' + retries + ' attempts');
        } catch (error) {
            if (i === retries - 1) throw error;
            await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1))); // Exponential backoff
        }
    }
}
```

#### ⚠️ **Missing Null Checks Can Cause Crashes**

**Example (Line 558):**
```javascript
function calculatePolicy() {
    const govNumberElement = document.getElementById('gov_number');
    if (!govNumberElement || !govNumberElement.value) return; // Good check

    const govPrefix = govNumberElement.value.trim().substring(0, 2);
```

**Good:** Checks element exists and has value

**Example (Line 565):**
```javascript
state.period = parseFloat(elements.periodSelect?.value) || 1;
```

**Good:** Uses optional chaining and fallback

**Example (Line 760):**
```javascript
if (driverInfoDisplay) {
    driverInfoDisplay.innerHTML += driverHtml;
}
```

**Good:** Checks element exists

**Overall:** Null checks are generally present, **BUT:**

**Line 432-456: `checkApplicantInfo()` function**
```javascript
ownerFields.forEach(field => {
    const ownerEl = document.getElementById(`insurance-${field}`);
    const applicantEl = document.getElementById(`applicant-${field}`);
    if (ownerEl && applicantEl) {
        applicantEl.value = ownerEl.value || '';
    }
    // Then OUTSIDE the if block:
    if (field === 'pinfl') {
        const applicantEl = document.getElementById(`applicant-pinfl`);
        applicantEl.value = ownerEl.value || ''; // ❌ ownerEl might be null here!
```

**Bug:** Checks `ownerEl && applicantEl` but then uses `ownerEl` outside the if block - could crash if `ownerEl` is null

---

## 7. Recommended Improvements (High-Level)

### A. Architectural Refactor

#### **1. Extract JavaScript to Separate Module**

**Current:** 805 lines inline in Blade template
**Recommended:**
```
/resources/js/osago/
├── main.js               // Entry point
├── state.js              // State management
├── api.js                // API calls
├── validators.js         // Input validation
├── formatters.js         // Data formatting (dates, currency, masks)
├── components/
│   ├── VehicleSearch.js
│   ├── OwnerSearch.js
│   ├── ApplicantSearch.js
│   ├── PolicyCalculation.js
│   └── DriverManagement.js
└── utils.js              // Utility functions
```

**Benefits:**
- Testable (unit tests for each module)
- Reusable (can use same code in other insurance products)
- Maintainable (clear separation of concerns)
- Can use modern build tools (Webpack, Vite) for minification, tree-shaking

#### **2. Implement Proper State Management**

**Current:** Minimal state object + hidden JSON inputs
**Recommended:** Centralized state with clear structure

```javascript
// state.js
class OSAGOFormState {
    constructor() {
        this.vehicle = null;
        this.owner = null;
        this.applicant = null;
        this.policy = {
            startDate: null,
            endDate: null,
            period: 1,
            amount: 0
        };
        this.drivers = [];
        this.isApplicantOwner = false;
        this.currentStep = 1;
        this.editMode = null;
    }

    // Methods
    setVehicle(data) {
        this.vehicle = data;
        this.emit('vehicle-updated');
    }

    setOwner(data) {
        this.owner = data;
        if (this.isApplicantOwner) {
            this.applicant = data; // Auto-sync
        }
        this.emit('owner-updated');
    }

    toggleApplicantIsOwner(checked) {
        this.isApplicantOwner = checked;
        if (checked) {
            this.applicant = this.owner;
        } else {
            this.applicant = null;
        }
        this.emit('applicant-updated');
    }

    addDriver(driver) {
        if (this.drivers.length >= 5) {
            throw new Error('Maximum 5 drivers allowed');
        }
        this.drivers.push(driver);
        this.emit('drivers-updated');
    }

    removeDriver(id) {
        this.drivers = this.drivers.filter(d => d.id !== id);
        this.emit('drivers-updated');
    }

    calculatePolicy() {
        // Calculation logic here
        this.policy.amount = /* ... */;
        this.emit('policy-updated');
    }

    // Event emitter
    listeners = {};
    on(event, callback) {
        if (!this.listeners[event]) this.listeners[event] = [];
        this.listeners[event].push(callback);
    }
    emit(event) {
        if (this.listeners[event]) {
            this.listeners[event].forEach(cb => cb());
        }
    }

    // Validation
    isValid() {
        return this.vehicle && this.owner && this.applicant && this.policy.amount > 0;
    }

    // Serialization for form submit
    toJSON() {
        return {
            vehicle: this.vehicle,
            owner: this.owner,
            applicant: this.applicant,
            policy: this.policy,
            drivers: this.drivers
        };
    }
}

export const formState = new OSAGOFormState();
```

**Benefits:**
- Single source of truth
- Automatic synchronization (e.g., isApplicantOwner updates applicant)
- Event-driven updates (UI listens to state changes)
- Easy to debug (inspect `formState` object in console)
- Easy to persist (save to localStorage for draft recovery)

#### **3. Component-Based UI Architecture**

**Recommended:** Each section is a self-contained component

```javascript
// VehicleSearch.js
export class VehicleSearch {
    constructor(formState) {
        this.state = formState;
        this.elements = {
            govNumber: document.getElementById('gov_number'),
            techSeries: document.getElementById('tech_passport_series'),
            techNumber: document.getElementById('tech_passport_number'),
            searchBtn: document.getElementById('vehicle-search-btn')
        };
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.restoreFromState();
    }

    attachEventListeners() {
        this.elements.searchBtn.addEventListener('click', () => this.handleSearch());
    }

    async handleSearch() {
        // Validation
        if (!this.validate()) return;

        // API call
        try {
            this.setLoading(true);
            const result = await vehicleAPI.search({
                govNumber: this.elements.govNumber.value,
                techSeries: this.elements.techSeries.value,
                techNumber: this.elements.techNumber.value
            });

            // Update state
            this.state.setVehicle(result);

            // Update UI (auto via state listener)
            this.showDisplay();
            this.lockInputs();
        } catch (error) {
            this.showError(error);
        } finally {
            this.setLoading(false);
        }
    }

    validate() {
        const validators = [
            { field: this.elements.govNumber, rule: validateGovNumber },
            { field: this.elements.techSeries, rule: validateTechSeries },
            { field: this.elements.techNumber, rule: validateTechNumber }
        ];

        for (const {field, rule} of validators) {
            const error = rule(field.value);
            if (error) {
                this.showFieldError(field, error);
                return false;
            }
        }
        return true;
    }

    enableEdit() {
        this.elements.govNumber.removeAttribute('readonly');
        this.elements.techSeries.removeAttribute('readonly');
        this.elements.techNumber.removeAttribute('readonly');
        this.showSection('vehicle-info-edit');
        this.hideSection('vehicle-info-display');
    }

    // ... more methods
}
```

### B. UX Improvements

#### **1. Clear Edit Flow**

**Add Edit Buttons to Each Completed Section:**
```html
<div id="vehicle-info-display" class="d-none">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>@lang('messages.vehicle_info')</h4>
        <button type="button" class="btn btn-outline-primary btn-sm" id="edit-vehicle-btn">
            <i class="fas fa-edit"></i> @lang('messages.edit')
        </button>
    </div>
    <!-- Vehicle data display -->
</div>
```

**Edit Behavior:**
1. Click Edit button
2. Show warning modal: "Editing will clear downstream data (Owner, Applicant, Drivers). Continue?"
3. If confirmed:
   - Unlock inputs
   - Clear downstream state (owner, applicant, drivers)
   - Hide downstream sections
   - Scroll to edit section
4. User re-searches/updates
5. New data flows forward again

#### **2. Step Navigation UI**

**Add Explicit Step Indicator:**
```html
<div class="steps-progress mb-4">
    <div class="step completed">
        <div class="step-number">1</div>
        <div class="step-label">@lang('messages.vehicle')</div>
    </div>
    <div class="step active">
        <div class="step-number">2</div>
        <div class="step-label">@lang('messages.owner')</div>
    </div>
    <div class="step disabled">
        <div class="step-number">3</div>
        <div class="step-label">@lang('messages.applicant')</div>
    </div>
    <!-- ... -->
</div>
```

**Step Transition Logic:**
```javascript
function goToStep(stepNumber) {
    // Validate current step before allowing transition
    if (!validateCurrentStep()) {
        showError('Please complete all required fields');
        return false;
    }

    // Update UI
    hideAllSteps();
    showStep(stepNumber);
    updateStepIndicator(stepNumber);

    // Update state
    formState.currentStep = stepNumber;

    // Scroll to top of form
    scrollToElement('osago-main');
}
```

#### **3. Uzbekistan-Specific Input Improvements**

**A. Passport Series + Number: Combined Input with Mask**
```html
<label>@lang('messages.passport')</label>
<input
    type="text"
    id="insurance-passport"
    placeholder="AA1234567"
    data-mask="AA-0000000"
    maxlength="10"
    pattern="[A-Z]{2}[0-9]{7}"
    required
    style="text-transform: uppercase">
<small class="form-text text-muted">
    @lang('messages.passport_hint') <!-- "Example: AA1234567 (2 letters + 7 digits)" -->
</small>
```

**JavaScript:**
```javascript
// Auto-uppercase and format
const passportInput = document.getElementById('insurance-passport');
passportInput.addEventListener('input', function(e) {
    let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    if (value.length > 2) {
        value = value.slice(0, 2) + '-' + value.slice(2, 9);
    }
    e.target.value = value;
});
```

**B. PINFL: Formatted with Spaces**
```html
<input
    type="text"
    id="insurance-pinfl"
    placeholder="12 345 67 89 01 23 4"
    data-mask="00 000 00 00 00 00 0"
    maxlength="19"
    pattern="[0-9]{2} [0-9]{3} [0-9]{2} [0-9]{2} [0-9]{2} [0-9]{2} [0-9]"
    required>
```

**C. Government Number: Regional Format**
```html
<input
    type="text"
    id="gov_number"
    placeholder="01 A 123 BC"
    maxlength="13"
    pattern="[0-9]{2} [A-Z] [0-9]{3} [A-Z]{2}"
    required
    style="text-transform: uppercase">
<small class="form-text text-muted">
    @lang('messages.gov_number_hint') <!-- "Example: 01 A 123 BC" -->
</small>
```

**JavaScript:**
```javascript
// Auto-format: 01A123BC → 01 A 123 BC
const govInput = document.getElementById('gov_number');
govInput.addEventListener('input', function(e) {
    let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

    if (value.length >= 2) {
        value = value.slice(0, 2) + ' ' + value.slice(2);
    }
    if (value.length >= 5) {
        value = value.slice(0, 5) + ' ' + value.slice(5);
    }
    if (value.length >= 9) {
        value = value.slice(0, 9) + ' ' + value.slice(9, 11);
    }

    e.target.value = value.slice(0, 13);
});
```

#### **4. Better Error Messages and Validation**

**Current:** Generic toast errors
**Recommended:** Field-specific inline errors

```javascript
function showFieldError(field, message) {
    // Add is-invalid class
    field.classList.add('is-invalid');

    // Create/update error message
    let errorDiv = field.nextElementSibling;
    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const errorDiv = field.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
        errorDiv.style.display = 'none';
    }
}
```

**Validation Messages (Uzbekistan-specific):**
```javascript
const VALIDATION_MESSAGES = {
    passport_series: 'Pasport seriyasi 2 ta harf bo\'lishi kerak (masalan: AA)', // "Passport series must be 2 letters"
    passport_number: 'Pasport raqami 7 ta raqam bo\'lishi kerak', // "Passport number must be 7 digits"
    pinfl: 'JSHSHIR 14 ta raqamdan iborat bo\'lishi kerak', // "PINFL must be 14 digits"
    gov_number: 'Davlat raqami noto\'g\'ri formatda (masalan: 01 A 123 BC)', // "Government number wrong format"
    phone: 'Telefon raqami noto\'g\'ri formatda (masalan: +998 90 123 45 67)' // "Phone number wrong format"
};
```

#### **5. Loading States and Skeleton Loaders**

**Current:** Spinner on button only
**Recommended:** Show skeleton loader for content being fetched

```html
<div id="vehicle-info-display" class="d-none">
    <!-- Skeleton loader shown during API call -->
    <div class="skeleton-loader" id="vehicle-info-skeleton">
        <div class="skeleton-line" style="width: 60%"></div>
        <div class="skeleton-line" style="width: 80%"></div>
        <div class="skeleton-line" style="width: 70%"></div>
    </div>

    <!-- Actual data shown after load -->
    <div id="vehicle-info-content" class="d-none">
        <!-- Vehicle details -->
    </div>
</div>
```

```javascript
async function handleVehicleSearch() {
    // Show skeleton
    showSection('vehicle-info-display');
    showSection('vehicle-info-skeleton');
    hideSection('vehicle-info-content');

    try {
        const result = await vehicleAPI.search(/*...*/);

        // Hide skeleton, show content
        hideSection('vehicle-info-skeleton');
        showSection('vehicle-info-content');
        populateVehicleInfo(result);
    } catch (error) {
        // Hide everything on error
        hideSection('vehicle-info-display');
        showError(error);
    }
}
```

### C. Data & Validation Improvements

#### **1. Input Mask Library Integration**

**Recommended:** Use `Inputmask` library (https://github.com/RobinHerbots/Inputmask)

**Installation:**
```bash
npm install inputmask
```

**Usage:**
```javascript
import Inputmask from 'inputmask';

// Passport: AA1234567
Inputmask({ mask: 'AA-9999999', placeholder: '', showMaskOnHover: false })
    .mask(document.getElementById('insurance-passport-series'));

// PINFL: 12 345 67 89 01 23 4
Inputmask({ mask: '99 999 99 99 99 99 9', placeholder: '_' })
    .mask(document.getElementById('insurance-pinfl'));

// Phone: +998 XX XXX XX XX
Inputmask({ mask: '+998 99 999 99 99', placeholder: '_' })
    .mask(document.getElementById('owner-phone'));

// Government number: 01 A 123 BC
Inputmask({ mask: '99 A 999 AA', placeholder: '', casing: 'upper' })
    .mask(document.getElementById('gov_number'));
```

#### **2. Comprehensive Validation Rules**

**Create validation module:**
```javascript
// validators.js
export const validators = {
    govNumber: (value) => {
        const cleaned = value.replace(/\s/g, '');
        if (!/^[0-9]{2}[A-Z][0-9]{3}[A-Z]{2}$/.test(cleaned)) {
            return 'messages.invalid_gov_number';
        }
        const region = parseInt(cleaned.slice(0, 2));
        if (region < 1 || region > 99) {
            return 'messages.invalid_region_code';
        }
        return null;
    },

    passportSeries: (value) => {
        if (!/^[A-Z]{2}$/.test(value)) {
            return 'messages.invalid_passport_series';
        }
        return null;
    },

    passportNumber: (value) => {
        if (!/^[0-9]{7}$/.test(value)) {
            return 'messages.invalid_passport_number';
        }
        return null;
    },

    pinfl: (value) => {
        const cleaned = value.replace(/\s/g, '');
        if (!/^[0-9]{14}$/.test(cleaned)) {
            return 'messages.invalid_pinfl';
        }
        // Optional: Add checksum validation for PINFL
        return null;
    },

    techPassportSeries: (value) => {
        if (!/^[A-Z]{2,3}$/.test(value)) {
            return 'messages.invalid_tech_series';
        }
        return null;
    },

    techPassportNumber: (value) => {
        if (!/^[0-9]{6,8}$/.test(value)) {
            return 'messages.invalid_tech_number';
        }
        return null;
    },

    phone: (value) => {
        const cleaned = value.replace(/\D/g, '');
        if (!cleaned.startsWith('998')) {
            return 'messages.invalid_country_code';
        }
        if (cleaned.length !== 12) {
            return 'messages.invalid_phone_length';
        }
        const operator = cleaned.slice(3, 5);
        const validOperators = ['88', '90', '91', '93', '94', '95', '97', '98', '99', '33'];
        if (!validOperators.includes(operator)) {
            return 'messages.invalid_operator_code';
        }
        return null;
    }
};
```

#### **3. Real-time Validation (Validate on Blur)**

```javascript
function attachValidation(inputElement, validatorFn) {
    inputElement.addEventListener('blur', function() {
        const error = validatorFn(this.value);
        if (error) {
            showFieldError(this, trans(error)); // trans() = Laravel translation helper
        } else {
            clearFieldError(this);
        }
    });

    // Clear error on input (give user immediate feedback)
    inputElement.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            clearFieldError(this);
        }
    });
}

// Attach to all fields
attachValidation(document.getElementById('gov_number'), validators.govNumber);
attachValidation(document.getElementById('insurance-passport-series'), validators.passportSeries);
// ... etc
```

#### **4. Fix Date Calculation Edge Cases**

```javascript
function updateEndDate() {
    if (!elements.startInput || !elements.endInput || !elements.periodSelect) return;

    // Get start date
    let startDate;
    if (!elements.startInput.value) {
        startDate = new Date();
        startDate.setHours(0, 0, 0, 0); // Normalize to midnight
        elements.startInput.value = formatDateForInput(startDate);
    } else {
        startDate = parseDateFromInput(elements.startInput.value);
    }

    // Get period
    const periodValue = elements.periodSelect.value;
    const monthsToAdd = CONFIG.MONTHS_MAP[periodValue] || 12;

    // Calculate end date: (start + N months - 1 day)
    const endDate = new Date(startDate);
    endDate.setMonth(endDate.getMonth() + monthsToAdd);
    endDate.setDate(endDate.getDate() - 1);

    // Handle edge case: if we ended up in wrong month (e.g., Jan 31 + 1 month = Mar 3)
    // Force to last day of correct month
    const expectedMonth = (startDate.getMonth() + monthsToAdd) % 12;
    if (endDate.getMonth() !== expectedMonth && endDate.getMonth() !== (expectedMonth + 1) % 12) {
        endDate.setDate(0); // Go to last day of previous month
    }

    elements.endInput.value = formatDateForInput(endDate);
    calculatePolicy();
}

function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function parseDateFromInput(dateString) {
    const [year, month, day] = dateString.split('-').map(Number);
    return new Date(year, month - 1, day);
}
```

### D. Security Hardening

#### **1. Add CSRF Token Validation**

```javascript
async function sendPostRequest(url, data, retries = 3) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
        throw new Error('CSRF token not found. Please refresh the page.');
    }

    for (let attempt = 1; attempt <= retries; attempt++) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                return await response.json();
            }

            // Handle specific HTTP errors
            if (response.status === 419) {
                throw new Error('Session expired. Please refresh the page.');
            }

            const result = await response.json();
            throw new Error(result.message || `Request failed with status ${response.status}`);

        } catch (error) {
            if (attempt === retries) throw error;

            // Exponential backoff
            await new Promise(resolve => setTimeout(resolve, 1000 * Math.pow(2, attempt - 1)));
        }
    }
}
```

#### **2. Prevent XSS: Use createElement Instead of innerHTML**

```javascript
// BEFORE (Dangerous)
driverInfoDisplay.innerHTML += driverHtml;

// AFTER (Safe)
function addDriverCard(driverData, uniqueId) {
    const cardFooter = document.createElement('div');
    cardFooter.className = 'card-footer mb-3';
    cardFooter.setAttribute('data-id', uniqueId);

    const cardTitle = document.createElement('h4');
    cardTitle.className = 'card-title';
    cardTitle.textContent = trans('messages.driver_info_title');
    cardFooter.appendChild(cardTitle);

    const row = document.createElement('div');
    row.className = 'row mb-2';

    // Full name input
    const nameCol = document.createElement('div');
    nameCol.className = 'col-md-5';

    const nameLabel = document.createElement('label');
    nameLabel.setAttribute('for', `driver-${uniqueId}-full-name`);
    nameLabel.className = 'form-label';
    nameLabel.textContent = trans('messages.driver_full_name');

    const nameInput = document.createElement('input');
    nameInput.type = 'text';
    nameInput.className = 'form-input';
    nameInput.id = `driver-${uniqueId}-full-name`;
    nameInput.name = `driver_full_name[${uniqueId}]`;
    nameInput.value = driverData.fullName; // Safe: value is escaped
    nameInput.readonly = true;

    const hiddenInfoInput = document.createElement('input');
    hiddenInfoInput.type = 'hidden';
    hiddenInfoInput.name = `driver_full_info[${uniqueId}]`;
    hiddenInfoInput.value = JSON.stringify(driverData);

    nameCol.appendChild(nameLabel);
    nameCol.appendChild(nameInput);
    nameCol.appendChild(hiddenInfoInput);

    // Kinship select (similar structure)
    // ...

    row.appendChild(nameCol);
    // ... append other columns

    cardFooter.appendChild(row);
    document.getElementById('driver-info-display').appendChild(cardFooter);
}
```

#### **3. Server-Side Validation Reminder**

**Add comment in Blade file:**
```html
<!--
    SECURITY NOTE:
    All validations in this form are CLIENT-SIDE ONLY and can be bypassed.
    The backend MUST re-validate all data:
    - owner-infos, applicant-infos, other_info JSON fields
    - All visible form inputs
    - Cross-field validations (e.g., policy dates)
    - API data integrity

    See: app/Http/Requests/OsagoApplicationRequest.php
-->
```

### E. Performance Optimizations

#### **1. Debounce Input Validation**

```javascript
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Use for validation on input (not blur)
const debouncedValidation = debounce((input, validator) => {
    const error = validator(input.value);
    if (error) {
        showFieldError(input, trans(error));
    } else {
        clearFieldError(input);
    }
}, 500);

inputElement.addEventListener('input', function() {
    debouncedValidation(this, validators.govNumber);
});
```

#### **2. Lazy Load Driver Cards**

If many drivers (up to 5), instead of manipulating DOM directly:

```javascript
// Use DocumentFragment for batch DOM updates
function renderDrivers(drivers) {
    const fragment = document.createDocumentFragment();
    drivers.forEach(driver => {
        const card = createDriverCard(driver);
        fragment.appendChild(card);
    });

    const container = document.getElementById('driver-info-display');
    container.innerHTML = ''; // Clear old
    container.appendChild(fragment); // Single reflow
}
```

---

## 8. Proposed New Architecture / Refactor Plan

### Phase 1: Extract and Modularize JavaScript (Week 1)

**Goals:**
- Move all inline JS to separate files
- Set up build system (Vite/Laravel Mix)
- No functional changes yet, just organization

**Steps:**
1. Create `/resources/js/osago/` directory structure
2. Extract functions to modules:
   - `api.js` - All `sendPostRequest` calls
   - `validators.js` - All validation functions
   - `formatters.js` - Date, currency, mask functions
   - `state.js` - State management class
   - `dom-utils.js` - Show/hide/scroll helpers
3. Create entry point `main.js` that imports all modules
4. Update `main.blade.php` to include compiled JS: `<script src="{{ mix('js/osago-main.js') }}"></script>`
5. Test thoroughly - should work identically to before

**Deliverables:**
- Modular JavaScript files
- Unit tests for validators and formatters
- Build configuration (Vite/Webpack)

### Phase 2: Implement Proper State Management (Week 2)

**Goals:**
- Replace hidden JSON inputs with centralized state object
- Implement event-driven UI updates

**Steps:**
1. Create `OSAGOFormState` class (as shown in section 7A.2)
2. Refactor all functions to read/write from `formState` instead of DOM
3. Add state change listeners:
   ```javascript
   formState.on('vehicle-updated', () => {
       renderVehicleDisplay();
       enableOwnerSearch();
   });
   ```
4. Keep hidden inputs for form submission (populate from state on submit):
   ```javascript
   formEl.addEventListener('submit', function(e) {
       document.getElementById('owner-infos').value = JSON.stringify(formState.owner);
       document.getElementById('applicant-infos').value = JSON.stringify(formState.applicant);
       // ... etc
   });
   ```

**Deliverables:**
- `OSAGOFormState` class
- Migrated all data to state object
- Hidden inputs populated only on submit

### Phase 3: Add Uzbekistan-Specific UX (Week 3)

**Goals:**
- Implement input masks
- Add proper validation with localized messages
- Improve error display

**Steps:**
1. Install and configure Inputmask library
2. Add masks to all serial/number fields
3. Implement real-time validation (on blur)
4. Add inline error messages (Bootstrap's `.invalid-feedback`)
5. Create Uzbek and Russian translations for all error messages
6. Add helpful hints/placeholders to all inputs

**Deliverables:**
- All inputs have masks and validation
- Inline error messages in Uzbek/Russian
- User-friendly placeholders and hints

### Phase 4: Implement Edit Flows (Week 4)

**Goals:**
- Add Edit buttons to each completed section
- Handle edit scenarios correctly

**Steps:**
1. Add Edit buttons to vehicle-info-display, owner-info-display, applicant-info-display
2. Implement edit mode:
   ```javascript
   function editVehicle() {
       // Confirm with user
       if (!confirm(trans('messages.edit_vehicle_warning'))) return;

       // Clear downstream data
       formState.clearOwner();
       formState.clearApplicant();
       formState.clearDrivers();

       // Show edit form
       hideSection('vehicle-info-display');
       showSection('vehicle-info');
       unlockVehicleInputs();

       // Scroll to form
       scrollToElement('vehicle-info');
   }
   ```
3. Test all edit scenarios:
   - Edit vehicle after owner filled
   - Edit owner after applicant filled
   - Edit applicant after drivers added
   - Toggle "applicant is owner" multiple times

**Deliverables:**
- Edit buttons functional
- Edit warnings prevent data loss
- All edit scenarios tested

### Phase 5: Security Hardening (Week 5)

**Goals:**
- Fix XSS vulnerabilities
- Add retry logic
- Improve CSRF handling

**Steps:**
1. Replace all `innerHTML` with `createElement` or safe libraries (DOMPurify)
2. Add retry logic to API calls
3. Add CSRF token validation
4. Add logging for API errors (send to backend monitoring)
5. Security audit and penetration testing

**Deliverables:**
- No XSS vulnerabilities
- Robust error handling
- Security audit report

### Phase 6: UI Polish & Testing (Week 6)

**Goals:**
- Add loading states
- Improve visual feedback
- Comprehensive testing

**Steps:**
1. Add skeleton loaders for API calls
2. Add step progress indicator
3. Add success animations/feedback
4. Create comprehensive test plan:
   - Unit tests for all modules
   - Integration tests for API calls
   - E2E tests for user flows
5. Browser compatibility testing (Chrome, Firefox, Safari, Edge)
6. Mobile responsive testing
7. Performance testing (Lighthouse audit)

**Deliverables:**
- Polished UI with loading states
- Full test suite (unit + integration + E2E)
- Browser compatibility report
- Performance report

---

## 9. Code Snippets for Quick Wins

### Quick Win 1: Add Edit Buttons (5 minutes)

**In vehicle-info component:**
```html
<div id="vehicle-info-display" class="card mb-4 d-none">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">@lang('messages.vehicle_information')</h4>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editVehicle()">
            <i class="fas fa-edit"></i> @lang('messages.edit')
        </button>
    </div>
    <!-- ... rest of vehicle display ... -->
</div>
```

**In main.blade.php script:**
```javascript
function editVehicle() {
    if (confirm('@lang('messages.edit_vehicle_warning')')) {
        // Unlock inputs
        document.getElementById('gov_number').removeAttribute('readonly');
        document.getElementById('tech_passport_series').removeAttribute('readonly');
        document.getElementById('tech_passport_number').removeAttribute('readonly');

        // Hide display, show input
        hideSection('vehicle-info-display');
        showSection('vehicle-info');

        // Clear downstream sections
        hideSection('owner-info-display');
        hideSection('applicant-info');
        hideSection('policy-calculation');
        hideSection('confirmation');

        // Clear hidden inputs
        document.getElementById('owner-infos').value = '';
        document.getElementById('applicant-infos').value = '';

        scrollToElement('vehicle-info');
    }
}
```

### Quick Win 2: Auto-Uppercase for Serial Fields (10 minutes)

**Add after DOMContentLoaded:**
```javascript
// Auto-uppercase for all serial input fields
const serialFields = [
    'tech_passport_series',
    'insurance-passport-series',
    'applicant-passport-series',
    'driver-passport-series'
];

serialFields.forEach(id => {
    const field = document.getElementById(id);
    if (field) {
        field.style.textTransform = 'uppercase';
        field.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    }
});
```

### Quick Win 3: Inline Validation Errors (15 minutes)

**Add styles to CSS:**
```css
.form-input.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: none;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-input.is-invalid ~ .invalid-feedback {
    display: block;
}
```

**Add validation function:**
```javascript
function validateField(input, validatorFn, errorMessage) {
    input.addEventListener('blur', function() {
        const isValid = validatorFn(this.value);
        if (!isValid) {
            this.classList.add('is-invalid');
            let feedback = this.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                this.parentNode.appendChild(feedback);
            }
            feedback.textContent = errorMessage;
        } else {
            this.classList.remove('is-invalid');
        }
    });

    input.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
}

// Usage
validateField(
    document.getElementById('insurance-passport-series'),
    (value) => /^[A-Z]{2}$/.test(value),
    '@lang('errors.passport_series_format')'
);
```

### Quick Win 4: Confirm Dialog for Applicant Checkbox Toggle (5 minutes)

**Replace line 486-488:**
```javascript
if (elements.applicantInfoCheck) {
    elements.applicantInfoCheck.addEventListener('change', function() {
        // If unchecking and applicant data exists, confirm first
        if (!this.checked) {
            const applicantData = document.getElementById('applicant-infos').value;
            if (applicantData && applicantData.trim() !== '') {
                if (!confirm('@lang('messages.clear_applicant_warning')')) {
                    this.checked = true; // Revert checkbox
                    return;
                }
            }
        }
        checkApplicantInfo();
    });
}
```

### Quick Win 5: CSRF Token Validation (2 minutes)

**Replace line 782:**
```javascript
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (!csrfToken) {
    throw new Error('CSRF token missing. Please refresh the page.');
}
```

---

## 10. Summary of Changes / Work Done

### What Was Analyzed

**File:** `resources/views/pages/insurence/osago/main.blade.php` (880 lines, 275KB)

**Scope:**
- Complete Laravel Blade template with inline JavaScript for OSAGO insurance application
- Multi-step form workflow: Vehicle → Owner → Applicant → Policy → Drivers → Confirmation
- Integration with external APIs for vehicle and person data lookup
- Real-time policy calculation based on multiple factors
- Client-side form validation before submission

### Main Problems Identified

**1. Architecture Issues (Critical)**
- 805 lines of inline JavaScript impossible to test or maintain
- No separation between data logic and DOM manipulation
- Tightly coupled to HTML element IDs (magic strings throughout)
- State management is minimal and doesn't track form progress
- Hidden JSON inputs create dual source of truth with visible form fields

**2. UX/UI Issues (Major for Uzbekistan Users)**
- **No edit mechanism** after data is filled (inputs become readonly with no unlock button)
- **Confusing "applicant is owner" checkbox** that silently overwrites data
- **No clear step navigation** - progression is implicit via show/hide sections
- **Missing Uzbekistan-specific validations** for passport, PINFL, government number formats
- **No input masks** - users must guess correct format
- **Generic error messages** - no field-specific guidance
- **Date format confusion** - ISO format internally but users expect DD.MM.YYYY

**3. State Management Bugs (Critical)**
- **Edit vehicle after owner filled:** Cannot unlock readonly fields, must reload page
- **Edit owner after applicant filled:** Hidden JSON doesn't sync with visible inputs
- **Toggle "applicant is owner" multiple times:** Loses manual edits without warning
- **Driver limit changes:** No validation that limited drivers must be added
- **Hidden inputs vs visible inputs:** Can get out of sync, causing form submission errors

**4. Data Validation Issues (Major)**
- **No uppercase enforcement** for serial number fields (passport, tech passport, gov number)
- **No input masks** for formatted data (PINFL, phone, passport)
- **Client-side validation only** at form submit - easy to bypass
- **No real-time validation** feedback (only after submit)
- **Date calculation edge case:** Jan 31 + 1 month gives wrong result

**5. Security Concerns (Critical)**
- **Potential XSS vulnerability:** Using `innerHTML +=` with API data (line 760)
- **CSRF token not required:** If meta tag missing, request proceeds without error
- **No input sanitization:** Only `.trim()` applied before API calls
- **Race conditions possible:** Multiple rapid button clicks can cause duplicate API calls
- **Client-side validation can be bypassed:** Backend must re-validate (not shown in code)

**6. Code Quality Issues (Major)**
- Massive inline script block (805 lines) in Blade template
- Repeated patterns (populateVehicleInfo, populateOwnerInfo, populateApplicantInfo nearly identical)
- No error logging - failures only shown as toasts
- Hardcoded Uzbek text in JavaScript (not translatable)
- Global click handler risks matching unintended buttons

### Improvements Proposed

**Phase 1: Modularization (Week 1)**
- Extract 805 lines of inline JS to separate modules
- Set up build system (Vite/Laravel Mix)
- Create directory structure: api.js, validators.js, formatters.js, state.js, components/

**Phase 2: State Management (Week 2)**
- Create `OSAGOFormState` class as single source of truth
- Replace hidden JSON inputs with state object (populate only on submit)
- Implement event-driven UI updates (state changes trigger renders)
- Add step tracking, validation status, edit mode to state

**Phase 3: Uzbekistan UX (Week 3)**
- Add Inputmask library for all serial/number fields
- Implement auto-uppercase for passport series, tech passport series, gov number
- Add real-time validation with inline error messages (on blur)
- Create Uzbek and Russian translations for all errors and hints
- Add placeholders showing expected format (e.g., "AA1234567")

**Phase 4: Edit Flows (Week 4)**
- Add Edit buttons to each completed section
- Implement edit warnings: "Editing will clear downstream data"
- Handle all edit scenarios: edit vehicle after owner, edit owner after applicant, etc.
- Fix "applicant is owner" checkbox to warn before overwriting manual data

**Phase 5: Security (Week 5)**
- Replace `innerHTML` with `createElement` to prevent XSS
- Require CSRF token, throw error if missing
- Add retry logic with exponential backoff for API calls
- Implement race condition guards (disable buttons, check if request in progress)
- Add logging for API errors

**Phase 6: Polish & Testing (Week 6)**
- Add skeleton loaders for API call wait states
- Add step progress indicator at top of form
- Add success animations/confirmations
- Create unit tests (validators, formatters, state)
- Create E2E tests (full user flows)
- Browser and mobile compatibility testing

### Quick Wins (Can Implement Today)

**5 Quick Fixes:**
1. **Edit Buttons:** Add "Edit" button to vehicle-info-display that unlocks inputs (5 min)
2. **Auto-Uppercase:** Add input event listener to uppercase serial fields (10 min)
3. **Inline Errors:** Show validation errors next to fields, not just toasts (15 min)
4. **Checkbox Confirmation:** Warn before unchecking "applicant is owner" (5 min)
5. **CSRF Validation:** Require CSRF token, throw error if missing (2 min)

**Total time for quick wins: ~40 minutes**
**Impact: Significantly improved UX and security**

### Conclusion

The current `main.blade.php` file works functionally but has significant architectural, UX, and security issues. The 805-line inline JavaScript makes maintenance difficult and testing impossible. Uzbekistan-specific UX requirements (input formats, validations, language) are not properly addressed. Edit flows between steps are broken or missing.

**Recommended approach:**
1. Start with **Quick Wins** to address most critical issues today
2. Execute **Phase 1 (Modularization)** to enable proper development workflow
3. Execute **Phases 2-6** systematically to rebuild on solid foundation

**Estimated total effort:**
- Quick Wins: 1 hour
- Full refactor (Phases 1-6): 6 weeks for 1 developer
- Or 2 weeks with 3 developers working in parallel

**Priority:**
- **High:** Phases 1, 2, 4, 5 (Architecture, State, Edit Flows, Security)
- **Medium:** Phase 3 (Uzbekistan UX - very important for users but not blocking)
- **Low:** Phase 6 (Polish - nice to have)

The investment is worthwhile: after refactor, the codebase will be maintainable, testable, secure, and user-friendly for Uzbekistan customers.
