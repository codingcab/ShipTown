<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use JMac\Testing\Traits\AdditionalAssertions;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use AdditionalAssertions;
    use ResetsDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ray()->showApp();

        ray()->clearAll();
        ray()->className($this)->blue();
    }
}
