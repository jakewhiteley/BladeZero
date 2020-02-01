<?php

namespace Unseenco\Blade\Tests\Illuminate\View\Blade;

class BladeErrorTest extends AbstractBladeTestCase
{
    public function testErrorsAreCompiled()
    {
        $string = '
@error(\'email\')
    <span>{{ $message }}</span>
@enderror';
        $expected = '
<?php 

if ($__env->errorHandler(\'email\')) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__env->errorHandler(\'email\'); ?>
    <span><?php echo e($message); ?></span>
<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
 ?>';

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testErrorsWithBagsAreCompiled()
    {
        $string = '
@error(\'email\', \'customBag\')
    <span>{{ $message }}</span>
@enderror';
        $expected = '
<?php 

if ($__env->errorHandler(\'email\', \'customBag\')) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__env->errorHandler(\'email\', \'customBag\'); ?>
    <span><?php echo e($message); ?></span>
<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
 ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
