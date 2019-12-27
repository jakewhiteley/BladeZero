<?php

namespace Rapier\Tests\Illuminate\View\Blade;

class BladeElseAuthStatementsTest extends AbstractBladeTestCase
{
    public function testElseAuthStatementsAreCompiled()
    {
        $string = '@auth("api")
breeze
@elseauth("standard")
wheeze
@endauth';
        $expected = '<?php if($__env->authHandler("api")): ?>
breeze
<?php elseif($__env->authHandler("standard")): ?>
wheeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPlainElseAuthStatementsAreCompiled()
    {
        $string = '@auth("api")
breeze
@elseauth
wheeze
@endauth';
        $expected = '<?php if($__env->authHandler("api")): ?>
breeze
<?php elseif($__env->authHandler()): ?>
wheeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
