# Multi-Language Support (Multilang)

This Laravel application supports three languages:
- **Russian (ru)** - Default language
- **Uzbek (uz)** - O'zbek tili
- **English (en)** - English

## Features

### 1. Language Detection
- URL-based language detection (e.g., `/ru/`, `/uz/`, `/en/`)
- Session-based language persistence
- Browser language detection as fallback
- Default fallback to Russian

### 2. URL Structure
- **With language prefix**: `/ru/`, `/uz/osago`, `/en/`
- **Default route**: `/` redirects to `/ru/`
- **Fallback route**: `/fallback` for backward compatibility

### 3. Translation System
- Translation files located in `resources/lang/{locale}/messages.php`
- Helper functions for easy translation
- View composer for shared locale service

## Usage

### In Views
```blade
<!-- Using Laravel's built-in translation helper -->
{{ __('messages.welcome') }}

<!-- Using custom helper function -->
{{ __t('messages.welcome') }}

<!-- With parameters -->
{{ __('messages.welcome_user', ['name' => $user->name]) }}

<!-- Get current locale -->
{{ getCurrentLocale() }}

<!-- Get localized URL -->
<a href="{{ getLocalizedUrl('uz') }}">O'zbek</a>
```

### In Controllers
```php
use App\Services\LocaleService;

public function index(LocaleService $localeService)
{
    $currentLocale = $localeService->getCurrentLocale();
    $availableLocales = $localeService->getAvailableLocales();
    
    return view('welcome', compact('currentLocale', 'availableLocales'));
}
```

### Language Switcher Component
The language switcher is automatically available in all views through the `$localeService` variable:

```blade
@foreach($localeService->getAvailableLocales() as $locale)
    @if($locale !== $localeService->getCurrentLocale())
        <a href="{{ $localeService->getLocalizedUrl($locale) }}">
            {{ $locale }}
        </a>
    @endif
@endforeach
```

## File Structure

```
resources/lang/
├── ru/
│   └── messages.php    # Russian translations
├── uz/
│   └── messages.php    # Uzbek translations
└── en/
    └── messages.php    # English translations

app/
├── Services/
│   └── LocaleService.php    # Language service
├── Http/Middleware/
│   └── SetLocale.php        # Language detection middleware
└── helpers.php              # Helper functions

routes/
├── web.php                  # Main routes with language prefixes
└── insurence/
    └── osago.php           # OSAGO routes with language support
```

## Adding New Translations

1. Add translation keys to all language files in `resources/lang/{locale}/messages.php`
2. Use the translation in views with `{{ __('messages.key_name') }}`

Example:
```php
// resources/lang/ru/messages.php
return [
    'new_key' => 'Новый текст',
    // ...
];

// resources/lang/uz/messages.php
return [
    'new_key' => 'Yangi matn',
    // ...
];

// resources/lang/en/messages.php
return [
    'new_key' => 'New text',
    // ...
];
```

## Configuration

### App Configuration
- Default locale: Russian (`ru`)
- Fallback locale: Russian (`ru`)
- Middleware: `SetLocale` automatically applied to web routes

### Environment Variables
```env
APP_LOCALE=ru
APP_FALLBACK_LOCALE=ru
```

## Routes

### Language Routes
```php
// Language-prefixed routes
Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'ru|uz|en']], function () {
    Route::get('/', function ($locale) {
        App::setLocale($locale);
        return view('welcome');
    })->name('home');
});
```

### Default Routes
```php
// Redirects to Russian by default
Route::get('/', function () {
    return redirect()->route('home', ['locale' => 'ru']);
});
```

## Testing

To test the multi-language functionality:

1. Visit `/` - should redirect to `/ru/`
2. Visit `/uz/` - should show Uzbek interface
3. Visit `/en/` - should show English interface
4. Use the language switcher in the header
5. Check that URLs maintain language context

## Notes

- All translations are stored in the `messages.php` files
- The system automatically detects language from URL, session, or browser
- Language preference is saved in session for consistency
- The `LocaleService` provides centralized language management
- Helper functions make translation usage more convenient

