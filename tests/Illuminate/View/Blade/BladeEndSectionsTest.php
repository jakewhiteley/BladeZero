<?php

namespace Bladezero\Tests\Illuminate\View\Blade;

class BladeEndSectionsTest extends AbstractBladeTestCase
{
    public function testEndSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->stopSection(); ?>', $this->compiler->compileString('@endsection'));
    }
}
