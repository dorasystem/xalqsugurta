<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JobApplicationResource\Pages;
use App\Models\JobApplication;
use App\Models\Vacancy;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

/** CVs sent from the career page (/{locale}/career); files open through a signed 30-minute link */
class JobApplicationResource extends Resource
{
    protected static ?string $model = JobApplication::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Karyera';

    protected static ?string $navigationLabel = 'Rezyumelar';

    protected static ?string $modelLabel = 'Rezyume';

    protected static ?string $pluralModelLabel = 'Rezyumelar';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = JobApplication::where('status', JobApplication::STATUS_NEW)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('vacancy'))
            ->columns([
                TextColumn::make('created_at')->label('Sana')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('name')->label('F.I.Sh.')->searchable()
                    ->description(fn (JobApplication $record): ?string => $record->email),
                TextColumn::make('phone')->label('Telefon')->searchable()
                    ->formatStateUsing(fn (?string $state) => $state ? formatPhone($state) : null)
                    ->url(fn (JobApplication $record) => 'tel:+' . $record->phone),
                TextColumn::make('vacancy.title_uz')->label('Vakansiya')->placeholder('Umumiy rezyume')->wrap(),
                TextColumn::make('message')->label('Izoh')->limit(80)->wrap()->placeholder('—'),
                TextColumn::make('status')->label('Holat')->badge()
                    ->formatStateUsing(fn (string $state) => $state === JobApplication::STATUS_REVIEWED ? 'Ko\'rib chiqildi' : 'Yangi')
                    ->color(fn (string $state) => $state === JobApplication::STATUS_REVIEWED ? 'success' : 'warning'),
                TextColumn::make('handler.name')->label('Xodim')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Holat')->options([
                    JobApplication::STATUS_NEW      => 'Yangi',
                    JobApplication::STATUS_REVIEWED => 'Ko\'rib chiqildi',
                ])->default(JobApplication::STATUS_NEW),
                SelectFilter::make('vacancy_id')->label('Vakansiya')
                    ->options(fn () => Vacancy::orderByDesc('published_at')->pluck('title_uz', 'id')->all()),
            ])
            ->recordActions([
                Action::make('resume')
                    ->label('Rezyume')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (JobApplication $record): string => URL::temporarySignedRoute('career.resume', now()->addMinutes(30), ['application' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn (JobApplication $record): bool => filled($record->file_path)),
                Action::make('reviewed')
                    ->label('Ko\'rib chiqildi')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (JobApplication $record) => $record->status === JobApplication::STATUS_NEW)
                    ->action(fn (JobApplication $record) => $record->update([
                        'status'     => JobApplication::STATUS_REVIEWED,
                        'handled_by' => Filament::auth()->id(),
                        'handled_at' => now(),
                    ])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobApplications::route('/'),
        ];
    }
}
