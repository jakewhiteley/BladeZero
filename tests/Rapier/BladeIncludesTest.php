<?php

namespace Rapier\Tests\Rapier;

class BladeIncludesTest extends AbstractBladeTestCase
{
    public function testEachDirective()
    {
        $projects = [
            ['name' => 'foo'],
            ['name' => 'bar'],
        ];

        $this->assertEquals(
            'foobar',
            $this->getCompiled('includes.each', compact('projects'))
        );

        $this->assertEquals(
            'none',
            $this->getCompiled('includes.each', ['projects' => []])
        );
    }
}
