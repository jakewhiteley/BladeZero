<?php

namespace Rapier\Tests\Rapier;

class BladeInjectTestTest extends AbstractBladeTestCase
{
    public function testInjectDirective()
    {
        $this->assertEquals(
            'Injected Foo\Bar',
            $this->getCompiled('inject.inject')
        );
    }
}
