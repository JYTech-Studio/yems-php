<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * 首頁導向後台儀表板（未登入會再被導到登入頁）。
     */
    public function test_the_root_redirects_to_dashboard(): void
    {
        $this->get('/')->assertRedirect(route('dashboard', absolute: false));
    }
}
