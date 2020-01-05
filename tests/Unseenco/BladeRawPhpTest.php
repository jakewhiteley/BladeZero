<?php

namespace Unseenco\Blade\Tests\Unseenco;

class BladeRawPhpTest extends AbstractBladeTestCase
{
    public function testPhpDirective()
    {
        $this->assertEquals(
            'foobar',
            $this->getCompiled('php.php')
        );
    }

    public function testUnsetDirective()
    {
        $this->assertEquals(
            'not set',
            $this->getCompiled('php.unset', ['foo' => 'foo'])
        );
    }

    public function testRawDirective()
    {
        $this->assertEquals(
            'foo',
            $this->getCompiled('php.raw')
        );
    }
}
