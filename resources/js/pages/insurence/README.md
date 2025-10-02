# OSAGO Main Page JavaScript

## Files

- `main.js` - Development version with comments and readable code
- `main.min.js` - Manually minified version (for reference)
- `README.md` - This documentation

## Features

- ✅ Form validation with real-time feedback
- ✅ Debounced input validation (300ms delay)
- ✅ Error message display with Bootstrap styling
- ✅ Auto-focus on first input
- ✅ Loading state for search button
- ✅ Optimized and minifiable code
- ✅ No external dependencies

## Usage

The JavaScript is automatically loaded on the OSAGO main page via Vite. No manual inclusion needed.

## Build Commands

```bash
# Development build
npm run build

# Production build with minification
npm run build:prod

# Minify only
npm run minify:js
```

## Validation Rules

- **Государственный номер**: Format `01A123B` (2 digits, 1 letter, 3 digits, 1 letter)
- **Серия тех.паспорта**: 3 uppercase letters
- **Номер тех.паспорта**: 10 digits
- **Год выпуска**: Year between 1900-2099

## Performance

- Minified size: ~2.3KB (gzipped: ~1KB)
- Uses modern JavaScript features
- Optimized for production builds

