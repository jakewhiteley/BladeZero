<?php

namespace Unseenco\Blade\Tests\Unseenco\Blade;

use PHPUnit\Framework\TestCase;
use Unseenco\Blade\Blade;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractBladeTestCase extends TestCase
{
    /**
     * @var \Unseenco\Blade\Blade
     */
    protected $compiler;

    protected function setUp(): void
    {
        $this->compiler = new Blade(
            dirname(__FILE__) . '/fixtures/files',
            dirname(__FILE__) . '/fixtures/cache'
        );

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();

        // clean cached files
        $fs->remove(dirname(__FILE__) . '/fixtures/cache/');
        $fs->mkdir(dirname(__FILE__) . '/fixtures/cache/');

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
