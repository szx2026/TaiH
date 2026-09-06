<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProjectTimeZoneTest extends TestCase
{
    public function test_the_application_uses_china_standard_time_for_project_timestamps(): void
    {
        $this->assertSame('Asia/Shanghai', config('app.timezone'));
    }
}
