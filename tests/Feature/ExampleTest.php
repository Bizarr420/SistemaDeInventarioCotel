<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_responds_for_index_route(): void
    {
        $response = $this->get('/');

        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }
}
