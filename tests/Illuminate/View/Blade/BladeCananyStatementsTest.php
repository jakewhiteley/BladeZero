<?php

namespace Unseenco\Blade\Tests\Illuminate\View\Blade;

class BladeCananyStatementsTest extends AbstractBladeTestCase
{
    public function testCananyStatementsAreCompiled()
    {
        $string = '@canany ([\'create\', \'update\'], [$post])
breeze
@elsecanany([\'delete\', \'approve\'], [$post])
sneeze
@endcan';
        $expected = '<?php if ($__env->canHandlerAny([\'create\', \'update\'], [$post])): ?>
breeze
<?php elseif ($__env->canHandlerAny([\'delete\', \'approve\'], [$post])): ?>
sneeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
