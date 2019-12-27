<?php

namespace Rapier\Tests\Rapier;

class BladeStacksTest extends AbstractBladeTestCase
{
    public function testStackDirective()
    {
        $this->assertEquals(
            'first second',
            $this->getCompiled('stacks.stack')
        );
    }
}
