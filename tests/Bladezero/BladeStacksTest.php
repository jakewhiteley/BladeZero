<?php

namespace Bladezero\Tests\Bladezero;

class BladeStacksTest extends AbstractBladeTestCase
{
    public function testStackDirective()
    {
        $this->assertEquals(
            'first second',
            $this->getCompiled('stacks.stack')
        );
    }

    public function testOnceDirective()
    {
        $this->assertEquals(
            'first',
            $this->getCompiled('stacks.once')
        );
    }
}
