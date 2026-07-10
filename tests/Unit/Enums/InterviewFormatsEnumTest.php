<?php

namespace Tests\Unit\Enums;

use App\Enums\InterviewFormatsEnum;
use PHPUnit\Framework\TestCase;

class InterviewFormatsEnumTest extends TestCase
{
    public function test_includes_coding_platform_format(): void
    {
        $this->assertSame('coding-platform', InterviewFormatsEnum::CodingPlatform->value);
        $this->assertContains(InterviewFormatsEnum::CodingPlatform, InterviewFormatsEnum::cases());
    }

    public function test_includes_ai_interview_format(): void
    {
        $this->assertSame('ai-interview', InterviewFormatsEnum::AiInterview->value);
        $this->assertContains(InterviewFormatsEnum::AiInterview, InterviewFormatsEnum::cases());
    }
}
