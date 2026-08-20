<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La raíz del sitio redirige a /login (no hay landing pública): ver
     * routes/web.php. Este test verificaba un 200 que ya no existe desde que
     * se agregó ese redirect.
     */
    public function test_the_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
