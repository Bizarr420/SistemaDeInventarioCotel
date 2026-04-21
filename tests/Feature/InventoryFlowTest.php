<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InventoryFlowTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_homepage_returns_expected_status(): void
    {
        $response = $this->get('/');

        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }
}
