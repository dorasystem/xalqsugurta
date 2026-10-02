<?php

namespace App\Filament\Admin\Pages;

use App\Services\SiteSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

/** Site-wide settings (App\Services\SiteSettings) */
class SiteSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|\UnitEnum|null $navigationGroup = 'Tizim';

    protected static ?string $navigationLabel = 'Sayt sozlamalari';

    protected static ?string $title = 'Sayt sozlamalari';

    protected static ?string $slug = 'site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSettings::formValues());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Ijtimoiy tarmoqlar')
                    ->icon('heroicon-o-share')
                    ->description('Saytning yon panelida, mobil menyuda ko\'rinadi. Bo\'sh qoldirilgan tarmoq belgisi ko\'rsatilmaydi.')
                    ->schema(collect(SiteSettings::SOCIAL)->map(fn (string $label, string $network) => TextInput::make('site.social.' . $network)
                        ->label($label)
                        ->url()
                        ->regex('#^https://#')
                        ->maxLength(255)
                        ->placeholder('https://…'))->values()->all()),

                Section::make('Bosh sahifa raqamlari')
                    ->icon('heroicon-o-chart-bar')
                    ->description('Bosh sahifadagi "50+ filial" kabi raqamlar. Faqat tasdiqlangan ma\'lumot yozing. Qiymati bo\'sh qatorni sayt ko\'rsatmaydi.')
                    ->schema(collect(range(1, SiteSettings::STATS))->map(fn (int $i) => Grid::make(4)->schema([
                        TextInput::make('site.stats.' . $i . '.value')->label($i . '. Qiymat')->maxLength(12)->placeholder('50+'),
                        TextInput::make('site.stats.' . $i . '.label_uz')->label('Matn (UZ)')->maxLength(60),
                        TextInput::make('site.stats.' . $i . '.label_ru')->label('Matn (RU)')->maxLength(60),
                        TextInput::make('site.stats.' . $i . '.label_en')->label('Matn (EN)')->maxLength(60),
                    ]))->all()),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Text::make('O\'zgarishlar saytda darhol ko\'rinadi.'),
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
        SiteSettings::save($this->form->getState());

        $this->form->fill(SiteSettings::formValues());

        Notification::make()->title('Saqlandi')->success()->send();
    }
}
