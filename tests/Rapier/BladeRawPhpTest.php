<?php

namespace Rapier\Tests\Rapier;

class BladeRawPhpTest extends AbstractBladeTestCase
{
    public function testPhpDirective()
    {
        $this->assertEquals(
            'foobar',
            $this->getCompiled('directives.php.php')
        );
    }

    public function testUnsetDirective()
    {
        $this->assertEquals(
            'not set',
            $this->getCompiled('directives.php.unset', ['foo' => 'foo'])
        );
    }

    public function testRawDirective()
    {
        $this->assertEquals(
            'foo',
            $this->getCompiled('directives.php.raw')
        );
    }
}
