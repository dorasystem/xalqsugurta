<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Pages\ServerCommandsPage;
use App\Models\CommandRun;
use App\Models\InfoPage;
use App\Models\User;
use App\Services\Admin\ServerCommands;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Tests\TestCase;

class ServerCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_opens_with_the_whitelisted_commands(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(ServerCommandsPage::getUrl())->assertOk()
            ->assertSee('Bazani yangilash (migratsiya)')
            ->assertSee('Kompaniya sahifalarini import qilish')
            ->assertSee('Hali buyruq bajarilmagan.');
    }

    public function test_start_records_a_run_and_refuses_a_second_one(): void
    {
        Bus::fake();
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(ServerCommandsPage::class)->call('start', 'migrate-status');
        $run = CommandRun::sole();
        $this->assertSame('migrate-status', $run->command);
        $this->assertSame(CommandRun::STATUS_RUNNING, $run->status);
        $this->assertSame($user->id, $run->user_id);

        Livewire::test(ServerCommandsPage::class)->call('start', 'import-news');
        $this->assertSame(1, CommandRun::count());

        // Unknown keys are ignored: nothing outside the list can be started
        $run->update(['status' => CommandRun::STATUS_DONE]);
        Livewire::test(ServerCommandsPage::class)->call('start', 'rm -rf /');
        $this->assertSame(1, CommandRun::count());
    }

    public function test_run_executes_the_steps_and_keeps_the_output(): void
    {
        $run = CommandRun::create(['command' => 'migrate-status', 'status' => CommandRun::STATUS_RUNNING, 'started_at' => now()]);

        ServerCommands::run($run);

        $run->refresh();
        $this->assertSame(CommandRun::STATUS_DONE, $run->status);
        $this->assertStringContainsString('$ php artisan migrate:status', $run->output);
        $this->assertStringContainsString('create_command_runs_table', $run->output);
        $this->assertNotNull($run->finished_at);
    }

    public function test_publish_pages_opens_only_pages_with_text(): void
    {
        InfoPage::create(['key' => 'about', 'section' => 'about', 'title_uz' => 'Biz haqimizda', 'body_uz' => '<p>Matn</p>']);
        InfoPage::create(['key' => 'audit', 'section' => 'about', 'title_uz' => 'Audit']);

        $run = CommandRun::create(['command' => 'publish-pages', 'status' => CommandRun::STATUS_RUNNING, 'started_at' => now()]);
        ServerCommands::run($run);

        $this->assertSame(['about'], InfoPage::publishedKeys());
        $this->assertStringContainsString('audit: matn yo\'q', $run->fresh()->output);
    }

    public function test_a_failing_command_is_marked_failed(): void
    {
        $run = CommandRun::create(['command' => 'unknown', 'status' => CommandRun::STATUS_RUNNING, 'started_at' => now()]);
        ServerCommands::run($run);
        $this->assertSame(CommandRun::STATUS_FAILED, $run->fresh()->status);
    }
}
