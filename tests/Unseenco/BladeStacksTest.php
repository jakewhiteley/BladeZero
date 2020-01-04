<?php

namespace Unseenco\Blade\Tests\Unseenco\Blade;

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
