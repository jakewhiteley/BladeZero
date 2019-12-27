<?php

namespace Rapier\Tests\Rapier;

class BladeLayoutsTest extends AbstractBladeTestCase
{
    public function testSectionWithDataDirective()
    {
        $this->assertEquals(
            'Page Title parent sidebar',
            $this->getCompiled('directives.layouts.section-with-data')
        );
    }

    public function testParentDirective()
    {
        $this->assertEquals(
            'before parent sidebar',
            $this->getCompiled('directives.layouts.parent')
        );
    }

    public function testOverwriteDirective()
    {
        $this->assertEquals(
            'overwritten',
            $this->getCompiled('directives.layouts.overwrite')
        );
    }

    public function testAppendDirective()
    {
        $this->assertEquals(
            'first second',
            $this->getCompiled('directives.layouts.append')
        );
    }
}
