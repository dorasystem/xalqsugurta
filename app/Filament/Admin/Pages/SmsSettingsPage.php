<?php

namespace App\Filament\Admin\Pages;

use App\Services\Sms\EskizClient;
use App\Services\Sms\SmsException;
use App\Services\SmsSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
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

/** Eskiz SMS for "Mening polislarim" codes (App\Services\SmsSettings) */
class SmsSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static string|\UnitEnum|null $navigationGroup = 'Tizim';

    protected static ?string $navigationLabel = 'SMS xabarlar';

    protected static ?string $title = 'SMS xabarlar (Eskiz)';

    protected static ?string $slug = 'sms';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SmsSettings::formValues());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Eskiz.uz')
                    ->description('"Mening polislarim" sahifasiga kirish kodlari shu orqali yuboriladi.')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->columns(2)
                    ->schema([
                        Toggle::make('eskiz.enabled')->label('Yoqilgan')->columnSpanFull(),
                        TextInput::make('eskiz.email')->label('Eskiz login (email)')->email(),
                        TextInput::make('eskiz.password')
                            ->label('Eskiz paroli')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->placeholder(fn (Get $get): string => $get('eskiz.password_saved') ? '•••••••• (saqlangan)' : 'Kiritilmagan')
                            ->helperText('Bo\'sh qoldirilsa, avvalgi parol o\'zgarmaydi.'),
                        TextInput::make('eskiz.from')->label('Jo\'natuvchi (nik)')->helperText('Eskiz kabinetidagi nik, standart: 4546'),
                        Textarea::make('eskiz.template')
                            ->label('Xabar matni')
                            ->rows(2)
                            ->helperText('{code} o\'rniga kod qo\'yiladi. Matn Eskiz\'da tasdiqlangan shablonga aynan mos bo\'lishi kerak, aks holda SMS yuborilmaydi.')
                            ->rule(fn () => fn (string $attribute, $value, \Closure $fail) => blank($value) || str_contains((string) $value, '{code}') ? null : $fail('Matnda {code} bo\'lishi kerak.'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Text::make('Bo\'sh qoldirilgan maydon serverdagi .env qiymatini ishlatadi.'),
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')
                ->label('Sinov SMS yuborish')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->modalDescription('Saqlangan sozlamalar bilan xabar matni (kod 123456) yuboriladi.')
                ->schema([
                    TextInput::make('phone')
                        ->label('Telefon')
                        ->placeholder('998901234567')
                        ->required()
                        ->regex('/^998[0-9]{9}$/'),
                ])
                ->action(fn (array $data) => $this->sendTest($data['phone'])),
        ];
    }

    public function save(): void
    {
        SmsSettings::save($this->form->getState());

        $this->form->fill(SmsSettings::formValues());

        Notification::make()->title('Saqlandi')->success()->send();
    }

    /** Sends the template with a sample code, so the login and the approved text are checked together */
    private function sendTest(string $phone): void
    {
        try {
            app(EskizClient::class)->send($phone, str_replace('{code}', '123456', (string) config('services.eskiz.template')));
        } catch (SmsException $e) {
            Notification::make()->title('SMS yuborilmadi')->body($e->getMessage())->danger()->send();

            return;
        }

        Notification::make()->title('SMS yuborildi')->success()->send();
    }
}
