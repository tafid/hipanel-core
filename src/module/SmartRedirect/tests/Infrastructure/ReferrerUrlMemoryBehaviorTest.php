<?php

declare(strict_types=1);

namespace hipanel\module\SmartRedirect\tests\Infrastructure;

use hipanel\module\SmartRedirect\Infrastructure\ReferrerUrlMemoryBehavior;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ReferrerUrlMemoryBehaviorTest extends TestCase
{
    public function testDefaultPatternsMatchIndexAndView(): void
    {
        $behavior = new ReferrerUrlMemoryBehavior();

        $this->assertTrue($this->isSuitableReferrer($behavior, '/entity/index'));
        $this->assertTrue($this->isSuitableReferrer($behavior, '/entity/view?id=1'));
        $this->assertFalse($this->isSuitableReferrer($behavior, '/entity/create'));
    }

    public function testCustomPatternsOverrideDefaults(): void
    {
        $behavior = new ReferrerUrlMemoryBehavior();
        $behavior->suitableReferrerPatterns = ['/custom'];

        $this->assertTrue($this->isSuitableReferrer($behavior, '/entity/custom'));
        $this->assertFalse($this->isSuitableReferrer($behavior, '/entity/index'));
    }

    private function isSuitableReferrer(ReferrerUrlMemoryBehavior $behavior, string $referrer): bool
    {
        $method = new ReflectionMethod($behavior, 'suitableReferrer');

        return $method->invoke($behavior, $referrer);
    }
}
