<?php

namespace Tests\Feature\Modules\Forge;

use App\Modules\Forge\src\Jobs\CreateSiteJob;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_module_basic_functionality()
    {
        // We just need to make sure that no exceptions are thrown when we run the module.
        CreateSiteJob::dispatch('demo.products.management.com');

        $this->assertTrue(true);
    }
}
