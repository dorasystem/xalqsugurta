<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_not_found_page_is_branded_in_the_url_locale(): void
    {
        $this->get('/uz/no-such-page')->assertNotFound()->assertSee('Sahifa topilmadi')->assertSee('href="/uz"', false);
        $this->get('/ru/no-such-page')->assertNotFound()->assertSee('Страница не найдена')->assertSee('href="/ru"', false);
        $this->get('/no-such-page')->assertNotFound()->assertSee('Sahifa topilmadi');
    }

    public function test_server_error_page_hides_the_exception(): void
    {
        config(['app.debug' => false]);
        Route::get('/en/boom', fn () => throw new \RuntimeException('boom'));

        $this->get('/en/boom')->assertStatus(500)->assertSee('Something went wrong')->assertDontSee('boom');
    }
}
