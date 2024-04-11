<?php

namespace Bladezero\Tests\Illuminate\View\Blade;

use Exception;
use Bladezero\Support\Fluent;
use Bladezero\Support\Str;

class BladeEchoHandlerTest extends AbstractBladeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler->stringable(function (Fluent $object) {
            return 'Hello World';
        });
    }

    public function testBladeHandlerCanInterceptRegularEchos()
    {
        $this->assertSame(
            "<?php \$__bladeCompiler = \Bladezero\Factory::getCompiler(); ?><?php echo e(\$__bladeCompiler->applyEchoHandler(\$exampleObject)); ?>",
            $this->compiler->compileString('{{$exampleObject}}')
        );
    }

    public function testBladeHandlerCanInterceptRawEchos()
    {
        $this->assertSame(
            "<?php \$__bladeCompiler = \Bladezero\Factory::getCompiler(); ?><?php echo \$__bladeCompiler->applyEchoHandler(\$exampleObject); ?>",
            $this->compiler->compileString('{!!$exampleObject!!}')
        );
    }

    public function testBladeHandlerCanInterceptEscapedEchos()
    {
        $this->assertSame(
            "<?php \$__bladeCompiler = \Bladezero\Factory::getCompiler(); ?><?php echo e(\$__bladeCompiler->applyEchoHandler(\$exampleObject)); ?>",
            $this->compiler->compileString('{{{$exampleObject}}}')
        );
    }

    public function testWhitespaceIsPreservedCorrectly()
    {
        $this->assertSame(
            "<?php \$__bladeCompiler = \Bladezero\Factory::getCompiler(); ?><?php echo e(\$__bladeCompiler->applyEchoHandler(\$exampleObject)); ?>\n\n",
            $this->compiler->compileString("{{\$exampleObject}}\n")
        );
    }

    /**
     * @dataProvider handlerLogicDataProvider
     */
    public function testHandlerLogicWorksCorrectly($blade)
    {
        $this->markTestSkipped();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The fluent object has been successfully handled!');

        $this->compiler->stringable(Fluent::class, function ($object) {
            throw new Exception('The fluent object has been successfully handled!');
        });

        $fakeInstance = new \Bladezero\Factory(realpath('./files'), realpath('./cache'));

        $exampleObject = new Fluent();

        eval(Str::of($this->compiler->compileString($blade))->remove(['<?php', '?>']));
    }

    public static function handlerLogicDataProvider()
    {
        return [
            ['{{$exampleObject}}'],
            ['{{$exampleObject;}}'],
            ['{{{$exampleObject;}}}'],
            ['{!!$exampleObject;!!}'],
        ];
    }

    /**
     * @dataProvider nonStringableDataProvider
     */
    public function testHandlerWorksWithNonStringables($blade, $expectedOutput)
    {
        $fakeInstance = new \Bladezero\Factory(realpath('./files'), realpath('./cache'));

        ob_start();
        eval(Str::of($this->compiler->compileString($blade))->remove(['<?php', '?>']));
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame($expectedOutput, $output);
    }

    public static function nonStringableDataProvider()
    {
        return [
            ['{{"foo" . "bar"}}', 'foobar'],
            ['{{ 1 + 2 }}{{ "test"; }}', '3test'],
            ['@php($test = "hi"){{ $test }}', 'hi'],
            ['{!! "&nbsp;" !!}', '&nbsp;'],
        ];
    }
}
