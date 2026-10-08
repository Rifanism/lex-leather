<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_landing_page_responds(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_the_health_endpoint_responds(): void
    {
        $this->get('/up')->assertOk();
    }
}
