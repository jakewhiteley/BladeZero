<?php

namespace Unseenco\Blade\Tests\Unseenco;

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
