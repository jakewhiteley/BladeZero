<?php

namespace Unseenco\Blade\Tests\Unseenco;

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

    public function testIncludeIfDirective()
    {
        $this->assertEquals(
            'none',
            $this->getCompiled('includes.includeif')
        );
    }

    public function testIncludeDirective()
    {
        $this->assertEquals(
            'first',
            $this->getCompiled('includes.include')
        );
    }

    public function testIncludeFirstDirective()
    {
        $this->assertEquals(
            'firstthird',
            $this->getCompiled('includes.includefirst')
        );
    }

    public function testIncludeWhenDirective()
    {
        $this->assertEquals(
            'first',
            $this->getCompiled('includes.includewhen')
        );
    }

    public function testIncludeUnlessDirective()
    {
        $this->assertEquals(
            'second',
            $this->getCompiled('includes.includeunless')
        );
    }
}
