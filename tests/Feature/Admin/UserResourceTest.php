<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Resources\UserResource;
use App\Filament\Admin\Resources\UserResource\Pages\CreateUser;
use App\Filament\Admin\Resources\UserResource\Pages\EditUser;
use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.admin_emails' => []]);
        $this->admin = User::factory()->create(['email' => 'boss@example.test']);
        $this->actingAs($this->admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_list_renders(): void
    {
        $this->get(UserResource::getUrl('index'))->assertOk()->assertSee('boss@example.test');
    }

    public function test_creates_user_with_hashed_password(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name'                  => 'Operator',
                'email'                 => 'operator@example.test',
                'password'              => 'Parol12345ab',
                'password_confirmation' => 'Parol12345ab',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'operator@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('Parol12345ab', $user->password));
    }

    public function test_rejects_weak_or_unconfirmed_password_and_duplicate_email(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name'                  => 'X',
                'email'                 => 'boss@example.test',
                'password'              => '123456789',
                'password_confirmation' => '123',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique', 'password']);
    }

    public function test_edit_keeps_password_when_left_empty(): void
    {
        $user = User::factory()->create(['password' => 'Eski12345abc']);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['name' => 'Yangi ism', 'password' => '', 'password_confirmation' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertSame('Yangi ism', $user->name);
        $this->assertTrue(Hash::check('Eski12345abc', $user->password));
    }

    public function test_edit_changes_password(): void
    {
        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['password' => 'Yangi12345ab', 'password_confirmation' => 'Yangi12345ab'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('Yangi12345ab', $user->fresh()->password));
    }

    public function test_cannot_delete_yourself_but_can_delete_others(): void
    {
        Livewire::test(EditUser::class, ['record' => $this->admin->getRouteKey()])
            ->assertActionHidden(DeleteAction::class);

        $other = User::factory()->create();
        Livewire::test(EditUser::class, ['record' => $other->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($other);
        $this->assertModelExists($this->admin);
    }

    public function test_list_shows_who_cannot_log_in(): void
    {
        config(['app.admin_emails' => ['boss@example.test']]);
        $blocked = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->assertTableColumnStateSet('panel_access', true, $this->admin)
            ->assertTableColumnStateSet('panel_access', false, $blocked);
    }
}
