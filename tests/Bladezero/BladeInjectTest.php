<?php

namespace Bladezero\Tests\Bladezero;

class BladeInjectTest extends AbstractBladeTestCase
{
    public function testInjectDirective()
    {
        $this->assertEquals(
            'Injected Foo\Bar',
            $this->getCompiled('inject.inject')
        );
    }
}
