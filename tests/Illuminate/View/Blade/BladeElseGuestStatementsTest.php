<?php

namespace Unseenco\Blade\Tests\Illuminate\View\Blade;

class BladeElseGuestStatementsTest extends AbstractBladeTestCase
{
    public function testIfStatementsAreCompiled()
    {
        $string = '@guest("api")
breeze
@elseguest("standard")
wheeze
@endguest';
        $expected = '<?php if(! $__env->authHandler("api")): ?>
breeze
<?php elseif(! $__env->authHandler("standard")): ?>
wheeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
