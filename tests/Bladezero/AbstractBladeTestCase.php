<?php

namespace Bladezero\Tests\Bladezero;

use PHPUnit\Framework\TestCase;
use Bladezero\Factory;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractBladeTestCase extends TestCase
{
    /**
     * @var \Bladezero\Factory
     */
    protected $compiler;

    protected function setUp(): void
    {
        $this->compiler = new Factory(
            __DIR__ . '/fixtures/files',
            __DIR__ . '/fixtures/cache'
        );

        Factory::setComponentNamespace('\\Bladezero\\Tests\\Bladezero\\Components\\');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();

        // clean cached files
       $fs->remove(__DIR__ . '/fixtures/cache/');
       $fs->mkdir(__DIR__ . '/fixtures/cache/');

        parent::tearDown();
    }

    public function getCompiled(string $path, array $data = [])
    {
        return \preg_replace(
            [
                '/[\r\n\t]+/',
                '/\s{2,}/'
            ],
            [
                '',
                ' '
            ],
            trim($this->compiler->make($path, $data))
        );
    }
}
