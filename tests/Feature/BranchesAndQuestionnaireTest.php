<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\BranchResource;
use App\Filament\Admin\Resources\SurveyResponseResource;
use App\Models\Branch;
use App\Models\SurveyResponse;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchesAndQuestionnaireTest extends TestCase
{
    use RefreshDatabase;

    // ─── Branches ──────────────────────────────────────────────────────────

    public function test_seeder_adds_the_old_sites_branches_once(): void
    {
        $this->seed(BranchSeeder::class);
        Branch::where('name_ru', 'Центральный аппарат')->update(['manager' => 'Edited']);
        $this->seed(BranchSeeder::class);

        $this->assertSame(16, Branch::count());
        $this->assertSame('Edited', Branch::where('name_ru', 'Центральный аппарат')->value('manager'));
        $this->assertSame(1, Branch::where('name_ru', 'Хорезмский областной филиал')->count());
        foreach (Branch::all() as $branch) {
            $this->assertNotEmpty($branch->name_uz);
            $this->assertNotEmpty($branch->name_en);
            $this->assertNotEmpty($branch->phones);
            $this->assertNotNull($branch->mapUrl());
        }
    }

    public function test_page_lists_active_branches_with_phones_and_map(): void
    {
        $this->seed(BranchSeeder::class);
        Branch::where('name_ru', 'Центр продажи')->update(['is_active' => false]);

        $this->get('/uz/branches')->assertOk()
            ->assertSeeInOrder(['Markaziy apparat', 'Qoraqalpog‘iston Respublikasi filiali'])
            ->assertSee('tel:+998712021966', false)
            ->assertSee('https://yandex.uz/maps/?pt=', false)
            ->assertSee('15 ta filial')
            ->assertDontSee('Savdo markazi');

        // Addresses exist in Russian only: shown on every language
        $this->get('/en/branches')->assertOk()->assertSee('Head office')->assertSee('Мирзо Улугбекский');
    }

    public function test_header_links_to_our_branches_and_questionnaire(): void
    {
        $this->get('/uz')->assertOk()
            ->assertSee(route('branches', ['locale' => 'uz']), false)
            ->assertSee(route('questionnaire', ['locale' => 'uz']), false);
    }

    // ─── Questionnaire ─────────────────────────────────────────────────────

    public function test_form_shows_the_three_questions(): void
    {
        $this->get('/ru/questionnaire')->assertOk()
            ->assertSee('Почему вы выбираете')
            ->assertSee('Страхование финансовых рисков')
            ->assertSee('Возраст компании');
    }

    public function test_answers_are_stored_with_the_ranking_in_order(): void
    {
        $this->post('/uz/questionnaire', [
            'reason'   => 'service',
            'products' => ['kasko', 'travel', 'kasko'],
            'rank'     => ['tariffs' => '1', 'service' => '2', 'age' => '3', 'rating' => ''],
        ])->assertRedirect(route('questionnaire', ['locale' => 'uz']));

        $response = SurveyResponse::sole();
        $this->assertSame('service', $response->reason);
        $this->assertSame(['kasko', 'travel'], $response->products);
        $this->assertSame(['tariffs', 'service', 'age'], $response->ranking);

        $this->get('/uz/questionnaire')->assertSee('Rahmat! Javoblaringiz qabul qilindi');
    }

    public function test_validation(): void
    {
        $this->post('/uz/questionnaire', ['reason' => 'other', 'products' => ['other'], 'rank' => ['age' => 1, 'rating' => 1]])
            ->assertSessionHasErrors(['reason_other', 'product_other', 'rank']);

        $this->post('/uz/questionnaire', ['reason' => 'nope'])->assertSessionHasErrors(['reason', 'products']);

        $this->post('/uz/questionnaire', ['website' => 'x', 'reason' => 'pays', 'products' => ['kasko']]);

        $this->assertSame(0, SurveyResponse::count());
    }

    // ─── Admin ─────────────────────────────────────────────────────────────

    public function test_admin_pages(): void
    {
        $this->seed(BranchSeeder::class);
        SurveyResponse::create(['reason' => 'pays', 'products' => ['osago', 'kasko'], 'ranking' => ['tariffs', 'age'], 'locale' => 'uz']);
        SurveyResponse::create(['reason' => 'pays', 'products' => ['osago'], 'ranking' => ['age'], 'locale' => 'ru']);
        $this->actingAs(User::factory()->create());

        $this->get(BranchResource::getUrl('index'))->assertOk()->assertSee('Markaziy apparat');
        $this->get(BranchResource::getUrl('create'))->assertOk();
        $this->get(SurveyResponseResource::getUrl('index'))->assertOk()
            ->assertSee('Natijalar: 2 ta javob')
            ->assertSee('Har doim to‘laydi');
    }
}
