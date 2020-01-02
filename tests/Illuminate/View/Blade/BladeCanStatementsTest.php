<?php

namespace Rapier\Tests\Illuminate\View\Blade;

class BladeCanStatementsTest extends AbstractBladeTestCase
{
    public function testCanStatementsAreCompiled()
    {
        $string = '@can (\'update\', [$post])
breeze
@elsecan(\'delete\', [$post])
sneeze
@endcan';
        $expected = '<?php if ($__env->canHandler(\'update\', [$post])): ?>
breeze
<?php elseif ($__env->canHandler(\'delete\', [$post])): ?>
sneeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
