<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** The bare domain sends visitors to the Uzbek home page, which renders */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertRedirect(route('home', ['locale' => 'uz']));

        $this->get('/uz')->assertOk();
    }
}
