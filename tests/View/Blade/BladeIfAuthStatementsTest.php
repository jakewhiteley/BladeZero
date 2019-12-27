<?php

namespace Rapier\Tests\View\Blade;

class BladeIfAuthStatementsTest extends AbstractBladeTestCase
{
    public function testIfStatementsAreCompiled()
    {
        $string = '@auth("api")
breeze
@endauth';
        $expected = '<?php if($__env->authHandler("api")): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPlainIfStatementsAreCompiled()
    {
        $string = '@auth
breeze
@endauth';
        $expected = '<?php if($__env->authHandler()): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
