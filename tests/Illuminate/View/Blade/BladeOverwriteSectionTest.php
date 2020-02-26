<?php

namespace Bladezero\Tests\Illuminate\View\Blade;

class BladeOverwriteSectionTest extends AbstractBladeTestCase
{
    public function testOverwriteSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->stopSection(true); ?>', $this->compiler->compileString('@overwrite'));
    }
}
