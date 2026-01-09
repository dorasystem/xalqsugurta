# OSAGO JavaScript Module - Documentation

## Overview

This directory contains the refactored, modular JavaScript code for the OSAGO (auto insurance) form. Previously, all 805 lines of JavaScript were inline in the Blade template. Now, the code is organized into logical modules that are testable, maintainable, and reusable.

## Directory Structure

```
resources/js/osago/
├── main.js                 // Entry point - initializes everything
├── config.js               // Configuration constants
├── state.js                // Centralized state management
├── api.js                  // API calls with retry logic
├── validators.js           // Uzbekistan-specific validation rules
├── formatters.js           // Date, currency, and input formatting
├── dom-utils.js            // DOM manipulation utilities
├── handlers/
│   ├── vehicle.js          // Vehicle search and management
│   ├── owner.js            // Owner information handling
│   ├── applicant.js        // Applicant information handling
│   ├── driver.js           // Driver management (add/remove)
│   └── policy.js           // Policy calculation
└── README.md               // This file
```

## Architecture

### State Management

The `OSAGOFormState` class (state.js) is the **single source of truth** for all form data:

```javascript
import formState from './state.js';

// Access state
console.log(formState.vehicle);
console.log(formState.owner);
console.log(formState.applicant);
console.log(formState.drivers);

// Listen to state changes
formState.on('vehicle-updated', (vehicleData) => {
    console.log('Vehicle updated:', vehicleData);
});

// Modify state
formState.setVehicle({...});
formState.setOwner({...});
formState.addDriver({...});
```

### Event-Driven Architecture

All handlers listen to state changes and update the UI accordingly:

```javascript
// Handler listens to state
formState.on('vehicle-updated', () => this.renderDisplay());

// When vehicle is searched and found
formState.setVehicle(vehicleData); // Triggers 'vehicle-updated' event
```

### API Module

All API calls go through the centralized API module with built-in:
- CSRF token validation
- Retry logic (3 attempts with exponential backoff)
- Race condition guards (prevents duplicate simultaneous requests)
- Error handling

```javascript
import { vehicleAPI, personAPI, driverAPI } from './api.js';

const result = await vehicleAPI.search(data);
```

### Validation

Uzbekistan-specific validation rules for:
- Government vehicle numbers (01 A 123 BC)
- Passport series/numbers (AA1234567)
- PINFL (14 digits)
- Tech passport series/numbers
- Phone numbers (+998 XX XXX XX XX)
- Driver licenses
- Dates and birth dates

```javascript
import * as validators from './validators.js';

const error = validators.validateGovNumber('01 A 123 BC');
if (error) {
    console.error(error);
}
```

### Formatters

Auto-formatting for inputs:
- Government numbers: `01A123BC` → `01 A 123 BC`
- PINFL: `12345678901234` → `12 345 67 89 01 23 4`
- Phone: `998901234567` → `+998 90 123 45 67`
- Currency: `168000` → `168 000.00`
- Dates: Proper ISO formatting and Uzbekistan display format

```javascript
import { formatGovNumber, formatPinfl, formatCurrency } from './formatters.js';

const formatted = formatGovNumber('01A123BC');
// Returns: '01 A 123 BC'
```

## Key Features

### 1. **Auto-Uppercase for Serial Fields**

Passport series, tech passport series, and government number letters are automatically converted to uppercase as the user types.

### 2. **Input Masks**

All Uzbekistan-specific formats have auto-formatting:
- Spaces are added automatically
- Invalid characters are prevented
- User-friendly input experience

### 3. **Real-Time Validation**

Validation happens on blur (when user leaves field):
- Inline error messages appear next to invalid fields
- Clear, specific error messages
- Errors clear automatically when user starts correcting

### 4. **Edit Flows**

Each completed section has an Edit button:
- Clicking Edit shows a warning about data loss
- Clearing a section cascades down (vehicle → owner → applicant → drivers)
- Inputs are unlocked for re-entry
- Previous sections can be safely edited

### 5. **Security**

- **XSS Prevention**: All dynamic content uses `createElement` instead of `innerHTML`
- **CSRF Protection**: All AJAX requests require valid CSRF token
- **Race Condition Guards**: Duplicate simultaneous requests are prevented
- **Input Sanitization**: Validation before API calls

### 6. **Error Handling**

- **Retry Logic**: Failed API calls retry up to 3 times with exponential backoff
- **Clear Error Messages**: User-friendly, specific error messages
- **Graceful Degradation**: Fallbacks when APIs fail

## Usage

### Building

```bash
# Development build (with hot reload)
npm run dev

# Production build (minified)
npm run build

# Production build with extra minification
npm run build:prod
```

### Adding to Blade Template

```blade
@push('scripts')
    @vite(['resources/js/osago/main.js'])

    <script>
        // Pass Laravel translations to JavaScript
        window.TRANSLATIONS = {
            car_type_2: '@lang('insurance.car_type_2')',
            edit_vehicle_warning: '@lang('messages.edit_vehicle_warning')',
            // ... more translations
        };
    </script>
@endpush
```

### Accessing Form State (for Debugging)

The form state is available globally for debugging:

```javascript
// In browser console
console.log(window.osagoFormState);
console.log(window.osagoFormState.vehicle);
console.log(window.osagoFormState.isValid());
```

## Handler Details

### Vehicle Handler

**Responsibilities:**
- Search vehicle by government number and tech passport
- Validate inputs before search
- Populate vehicle data from API
- Lock inputs after successful search
- Handle edit mode

**Events Emitted:**
- `vehicle-updated` - When vehicle data is set
- `vehicle-cleared` - When edit mode is triggered

### Owner Handler

**Responsibilities:**
- Search owner by passport and PINFL
- Populate owner data
- Auto-check "applicant is owner" checkbox
- Handle edit mode

**Events Emitted:**
- `owner-updated`
- `owner-cleared`

### Applicant Handler

**Responsibilities:**
- Handle "applicant is owner" checkbox
- Copy owner data to applicant when checked
- Search applicant independently if not owner
- Handle edit mode

**Events Emitted:**
- `applicant-updated`
- `applicant-cleared`
- `applicant-owner-toggled`

### Driver Handler

**Responsibilities:**
- Add drivers (up to 5)
- Remove drivers
- Handle unlimited vs limited driver radio buttons
- Render driver cards safely (no XSS)

**Events Emitted:**
- `driver-added`
- `driver-removed`
- `drivers-cleared`
- `driver-limit-changed`

### Policy Handler

**Responsibilities:**
- Calculate insurance premium
- Update policy dates based on period
- Determine region coefficient from government number
- Display calculated amounts

**Events Emitted:**
- `policy-updated`
- `policy-calculated`

## Testing

### Manual Testing Checklist

1. **Vehicle Search**
   - [ ] Valid government number works
   - [ ] Invalid format shows error
   - [ ] API failure shows error message
   - [ ] Success locks inputs and shows data

2. **Owner Search**
   - [ ] Valid passport/PINFL works
   - [ ] Invalid format shows error
   - [ ] Success populates owner data
   - [ ] "Applicant is owner" auto-checks

3. **Applicant**
   - [ ] Checkbox copies owner to applicant
   - [ ] Unchecking clears applicant
   - [ ] Manual search works independently
   - [ ] Warning shown before clearing

4. **Drivers**
   - [ ] Can add up to 5 drivers
   - [ ] Cannot add more than 5
   - [ ] Delete button works
   - [ ] Unlimited/Limited radio works
   - [ ] Period select disabled for unlimited

5. **Edit Flows**
   - [ ] Edit vehicle shows warning
   - [ ] Edit clears downstream data
   - [ ] Edit owner shows warning
   - [ ] Edit applicant shows warning
   - [ ] Inputs unlock properly

6. **Formatting**
   - [ ] Government number auto-formats
   - [ ] PINFL adds spaces
   - [ ] Passport series auto-uppercase
   - [ ] Currency displays correctly

7. **Validation**
   - [ ] Inline errors show on blur
   - [ ] Errors clear on input
   - [ ] Form submission validates
   - [ ] Specific error messages shown

### Unit Testing (Future)

The modular structure enables unit testing:

```javascript
// Example test
import { validateGovNumber } from './validators.js';

test('validateGovNumber accepts valid format', () => {
    const error = validateGovNumber('01 A 123 BC');
    expect(error).toBeNull();
});

test('validateGovNumber rejects invalid format', () => {
    const error = validateGovNumber('invalid');
    expect(error).toBe('Invalid format. Example: 01 A 123 BC');
});
```

## Troubleshooting

### Issue: JavaScript not loading

**Solution:** Ensure Vite is running (`npm run dev`) or assets are built (`npm run build`).

### Issue: Translations not working

**Solution:** Ensure `window.TRANSLATIONS` object is defined in the Blade template before loading the OSAGO script.

### Issue: API calls failing

**Solution:** Check browser console for CSRF token errors. Ensure `<meta name="csrf-token">` tag exists in layout.

### Issue: Form not submitting

**Solution:** Check browser console for validation errors. Use `window.osagoFormState.isValid()` to debug.

### Issue: Edit buttons not working

**Solution:** Ensure global functions (`editVehicle`, `editOwner`, `editApplicant`) are defined. They're attached in the handler modules.

## Migration from Old Code

### Before (Inline)

```blade
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 805 lines of inline JavaScript
        const searchBtn = document.getElementById('vehicle-search-btn');
        searchBtn.addEventListener('click', async function() {
            // ... API call ...
            // ... populate data ...
        });
        // ... more code ...
    });
</script>
@endpush
```

### After (Modular)

```blade
@push('scripts')
    @vite(['resources/js/osago/main.js'])
    <script>
        window.TRANSLATIONS = { /* ... */ };
    </script>
@endpush
```

All logic is now in modular, testable files.

## Performance

### Bundle Size

- **Old**: 805 lines inline (~40KB unminified)
- **New**: ~27KB minified, ~8KB gzipped

### Benefits

- Code splitting enabled
- Tree shaking removes unused code
- Minification and compression
- Browser caching (fingerprinted filenames)
- Parallel downloads (separate chunks)

## Future Enhancements

1. **TypeScript Migration**: Add type safety
2. **Unit Tests**: Add comprehensive test coverage
3. **E2E Tests**: Cypress/Playwright for full flows
4. **Accessibility**: ARIA labels, keyboard navigation
5. **Progressive Enhancement**: Work without JavaScript
6. **Offline Support**: Service worker for caching
7. **Analytics**: Track user interactions
8. **A/B Testing**: Test different UX approaches

## Contributing

When adding new features:

1. Create a new module file if needed
2. Follow the existing patterns (handlers listen to state)
3. Update this README
4. Add validation rules if needed
5. Add formatters if needed
6. Test thoroughly
7. Build assets before committing

## License

Internal use - Company proprietary

## Support

For questions or issues:
- Check this README
- Review `osago-main.MD` (technical analysis document)
- Check browser console for errors
- Contact the development team

---

**Last Updated:** 2025-11-24
**Version:** 2.0.0 (Modular Refactor)
