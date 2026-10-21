<?php

namespace App\Filament\Admin\Pages;

use App\Services\PaymentSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

/** Click / Payme credentials and on/off switches (App\Services\PaymentSettings) */
class PaymentSystems extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|\UnitEnum|null $navigationGroup = 'Tizim';

    protected static ?string $navigationLabel = 'To\'lov tizimlari';

    protected static ?string $title = 'To\'lov tizimlari';

    protected static ?string $slug = 'payment-systems';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(PaymentSettings::formValues());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(['lg' => 2])
            ->components([
                Section::make('Click')
                    ->description('Click kabinetidagi "Sozlamalar" bo\'limidan olinadi.')
                    ->icon('heroicon-o-bolt')
                    ->columns(2)
                    ->schema([
                        Toggle::make('click.enabled')
                            ->label('To\'lov sahifasida ko\'rsatish')
                            ->columnSpanFull(),
                        TextInput::make('click.service_id')
                            ->label('Service ID')
                            ->numeric(),
                        TextInput::make('click.merchant_id')
                            ->label('Merchant ID')
                            ->numeric(),
                        TextInput::make('click.merchant_user_id')
                            ->label('Merchant user ID')
                            ->numeric(),
                        $this->secretInput('click.secret_key', 'Secret key'),
                        Text::make(fn (): HtmlString => new HtmlString(
                            'Click kabinetiga kiritiladigan manzillar:<br>'
                            . 'Prepare: <code>' . e(url('/api/prepare')) . '</code><br>'
                            . 'Complete: <code>' . e(url('/api/complete')) . '</code>'
                        ))->columnSpanFull(),
                    ]),

                Section::make('Payme')
                    ->description('Payme Business kabinetidagi kassa sozlamalaridan olinadi.')
                    ->icon('heroicon-o-wallet')
                    ->columns(2)
                    ->schema([
                        Toggle::make('payme.enabled')
                            ->label('To\'lov sahifasida ko\'rsatish')
                            ->columnSpanFull(),
                        TextInput::make('payme.merchant_id')
                            ->label('Kassa ID (merchant ID)')
                            ->columnSpanFull(),
                        $this->secretInput('payme.secret_key', 'Kalit (production)'),
                        $this->secretInput('payme.test_secret_key', 'Test kaliti'),
                        Toggle::make('payme.test_mode')
                            ->label('Test rejimi')
                            ->helperText('Faqat Payme sandbox bilan sinov paytida yoqing.')
                            ->columnSpanFull(),
                        Text::make(fn (): HtmlString => new HtmlString(
                            'Payme kabinetiga kiritiladigan manzil: <code>' . e(route('payment.payme.callback')) . '</code>'
                        ))->columnSpanFull(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Text::make('Bo\'sh qoldirilgan maydon serverdagi .env qiymatini ishlatadi. Kalitlar shifrlangan holda saqlanadi va bu yerda qayta ko\'rsatilmaydi.'),
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Saqlash')->submit('save'),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        PaymentSettings::save($this->form->getState());

        $this->form->fill(PaymentSettings::formValues());

        Notification::make()->title('Saqlandi')->success()->send();
    }

    /** Password field: empty keeps the stored key, the helper says whether one is saved */
    private function secretInput(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->password()
            ->revealable()
            ->autocomplete('new-password')
            ->placeholder(fn (Get $get): string => $get($name . '_saved') ? '•••••••• (saqlangan)' : 'Kiritilmagan')
            ->helperText('Bo\'sh qoldirilsa, avvalgi kalit o\'zgarmaydi.');
    }
}
