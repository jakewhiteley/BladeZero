<?php

namespace Unseenco\Blade\Tests\Illuminate\View\Blade;

class BladeCanStatementsTest extends AbstractBladeTestCase
{
    public function testCanStatementsAreCompiled()
    {
        $string = '@can (\'update\', [$post])
breeze
@elsecan(\'delete\', [$post])
sneeze
@endcan';
        $expected = '<?php if (app(\\Unseenco\Blade\\Contracts\\Auth\\Access\\Gate::class)->check(\'update\', [$post])): ?>
breeze
<?php elseif (app(\\Unseenco\Blade\\Contracts\\Auth\\Access\\Gate::class)->check(\'delete\', [$post])): ?>
sneeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
