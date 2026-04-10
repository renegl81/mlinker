<?php

namespace Tests;

use App\Models\Plan;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // Reset Plan::free() memoization between tests (RefreshDatabase wipes the DB
        // but the static property would keep a stale reference).
        Plan::resetFreePlanCache();
    }
}
