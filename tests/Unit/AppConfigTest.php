<?php

namespace Tests\Unit;

use Illuminate\Support\Env;
use PHPUnit\Framework\TestCase;

class AppConfigTest extends TestCase
{
    public function test_app_timezone_can_be_configured_from_environment(): void
    {
        $environment = Env::getRepository();
        $previousTimezone = $environment->get('APP_TIMEZONE');

        $environment->set('APP_TIMEZONE', 'America/New_York');

        try {
            $config = require dirname(__DIR__, 2).'/config/app.php';

            $this->assertSame('America/New_York', $config['timezone']);
        } finally {
            if ($previousTimezone === null) {
                $environment->clear('APP_TIMEZONE');
            } else {
                $environment->set('APP_TIMEZONE', $previousTimezone);
            }
        }
    }
}
