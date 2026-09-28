<?php

namespace App\Filament\Admin\Pages;

use App\Services\ProviderSettings;
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
use Filament\Schemas\Schema;

/** Insurer API settings (App\Services\ProviderSettings) */
class ProviderApiSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-server-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Tizim';

    protected static ?string $navigationLabel = 'Sug\'urtachi API';

    protected static ?string $title = 'Sug\'urtachi API';

    protected static ?string $slug = 'provider-api';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(ProviderSettings::formValues());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('OSGOP va OSGOR')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        TextInput::make('provider.agency_id')
                            ->label('Agentlik ID (agencyId)')
                            ->numeric()
                            ->placeholder(fn (): string => '.env: ' . (ProviderSettings::envValue('provider.agency_id') ?? '—'))
                            ->helperText('Sug\'urta kompaniyasi beradi. Noto\'g\'ri bo\'lsa, API "The selected agency does not belong to your insurance organization" deb javob beradi.'),
                    ]),

                Section::make('OSAGO')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Toggle::make('provider.osago_legal_entities')
                            ->label('Yuridik shaxslarga sotish')
                            ->helperText('Egasi tashkilot bo\'lgan avtomobil uchun polis (INN bo\'yicha). Sug\'urtachi bunday body namunasini bermagan: yoqqandan keyin bitta sinov sotuvini qilib, API jurnalida javobni tekshiring.'),
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

    public function save(): void
    {
        ProviderSettings::save($this->form->getState());

        $this->form->fill(ProviderSettings::formValues());

        Notification::make()->title('Saqlandi')->success()->send();
    }
}
