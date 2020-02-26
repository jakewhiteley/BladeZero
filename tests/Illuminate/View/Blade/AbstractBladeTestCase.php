<?php

namespace Bladezero\Tests\Illuminate\View\Blade;

use Bladezero\Filesystem\Filesystem;
use Bladezero\View\Compilers\BladeCompiler;
use Mockery as m;
use PHPUnit\Framework\TestCase;

abstract class AbstractBladeTestCase extends TestCase
{
    /**
     * @var \Bladezero\View\Compilers\BladeCompiler
     */
    protected $compiler;

    protected function setUp(): void
    {
        $this->compiler = new BladeCompiler($this->getFiles(), __DIR__);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    protected function getFiles()
    {
        return m::mock(Filesystem::class);
    }
}
