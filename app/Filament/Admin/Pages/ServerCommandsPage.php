<?php

namespace App\Filament\Admin\Pages;

use App\Jobs\RunServerCommand;
use App\Models\CommandRun;
use App\Services\Admin\ServerCommands;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

/**
 * Whitelisted server commands (App\Services\Admin\ServerCommands) with a button each and the
 * history of runs. There is deliberately no free-form command line.
 */
class ServerCommandsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-command-line';

    protected static string|\UnitEnum|null $navigationGroup = 'Tizim';

    protected static ?string $navigationLabel = 'Server buyruqlari';

    protected static ?string $title = 'Server buyruqlari';

    protected static ?string $slug = 'server-commands';

    protected static ?int $navigationSort = 90;

    public function content(Schema $schema): Schema
    {
        $groups = collect(ServerCommands::COMMANDS)->groupBy(fn (array $c) => $c[2], preserveKeys: true);

        return $schema->components([
            Text::make('Buyruq bosilgandan keyin fonda bajariladi, natijasi pastdagi ro\'yxatda chiqadi. Bir vaqtda bitta buyruq ishlaydi.'),
            ...$groups->map(fn ($commands, string $group) => Section::make($group)
                ->schema([
                    Actions::make($commands->map(fn (array $c, string $key) => Action::make('run_' . str_replace('-', '_', $key))
                        ->label($c[0])
                        ->tooltip($c[1])
                        ->color(str_ends_with($key, '-force') ? 'danger' : 'primary')
                        ->outlined(!in_array($key, ['migrate', 'import-pages', 'publish-pages', 'import-news'], true))
                        ->requiresConfirmation()
                        ->modalHeading($c[0])
                        ->modalDescription($c[1])
                        ->modalSubmitActionLabel('Bajarish')
                        ->action(fn () => $this->start($key)))->values()->all()),
                ]))->values()->all(),
            View::make('filament.admin.command-runs')
                ->viewData(fn () => [
                    'runs'    => CommandRun::with('user')->latest('id')->limit(15)->get(),
                    'running' => ServerCommands::busy() !== null,
                ]),
        ]);
    }

    public function start(string $key): void
    {
        if (!isset(ServerCommands::COMMANDS[$key])) {
            return;
        }

        if ($busy = ServerCommands::busy()) {
            Notification::make()->danger()->title('Hozir "' . ServerCommands::label($busy->command) . '" bajarilmoqda')->body('Tugashini kuting.')->send();

            return;
        }

        $run = CommandRun::create([
            'command'    => $key,
            'status'     => CommandRun::STATUS_RUNNING,
            'user_id'    => Filament::auth()->id(),
            'started_at' => now(),
        ]);

        RunServerCommand::dispatch($run->id)->afterResponse();

        Notification::make()->success()->title('Boshlandi: ' . ServerCommands::label($key))->send();
    }
}
