<?php

namespace Tests\Unit\Actions;

use App\Actions\InterviewLengthToHumanLabel;
use App\Models\Interview;
use PHPUnit\Framework\TestCase;

class InterviewLengthToHumanLabelTest extends TestCase
{
    private InterviewLengthToHumanLabel $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new InterviewLengthToHumanLabel();
    }

    public function test_returns_dash_for_null_length(): void
    {
        $interview = new Interview(['length_minutes' => null]);
        $result = $this->action->handle($interview);

        $this->assertEquals('—', $result);
    }

    public function test_returns_dash_for_zero_length(): void
    {
        $interview = new Interview(['length_minutes' => 0]);
        $result = $this->action->handle($interview);

        $this->assertEquals('—', $result);
    }

    public function test_returns_minutes_only(): void
    {
        $interview = new Interview(['length_minutes' => 45]);
        $result = $this->action->handle($interview);

        $this->assertEquals('45m', $result);
    }

    public function test_returns_hours_only(): void
    {
        $interview = new Interview(['length_minutes' => 120]);
        $result = $this->action->handle($interview);

        $this->assertEquals('2h', $result);
    }

    public function test_returns_hours_and_minutes(): void
    {
        $interview = new Interview(['length_minutes' => 75]);
        $result = $this->action->handle($interview);

        $this->assertEquals('1h 15m', $result);
    }

    public function test_returns_hours_and_minutes_with_large_values(): void
    {
        $interview = new Interview(['length_minutes' => 155]);
        $result = $this->action->handle($interview);

        $this->assertEquals('2h 35m', $result);
    }

    public function test_returns_single_hour(): void
    {
        $interview = new Interview(['length_minutes' => 60]);
        $result = $this->action->handle($interview);

        $this->assertEquals('1h', $result);
    }

    public function test_returns_single_minute(): void
    {
        $interview = new Interview(['length_minutes' => 1]);
        $result = $this->action->handle($interview);

        $this->assertEquals('1m', $result);
    }
}
