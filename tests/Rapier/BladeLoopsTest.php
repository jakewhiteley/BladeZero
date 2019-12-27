<?php

namespace Rapier\Tests\Rapier;

class BladeLoopsTest extends AbstractBladeTestCase
{
    public function testForeachDirective()
    {
        $data = [
            'foo',
            'bar'
        ];

        $this->assertEquals(
            'foo bar',
            $this->getCompiled('directives.loops.foreach', compact('data'))
        );
    }

    public function testForelseDirective()
    {
        $users = [
            'jake',
            'luke'
        ];

        $this->assertEquals(
            '<li>jake</li> <li>luke</li>',
            $this->getCompiled('directives.loops.forelse', compact('users'))
        );

        $this->assertEquals(
            'No users',
            $this->getCompiled('directives.loops.forelse', ['users' => []])
        );
    }

    public function testForDirective()
    {
        $this->assertEquals(
            '0 1 2',
            $this->getCompiled('directives.loops.for')
        );
    }

    public function testContinueDirective()
    {
        $this->assertEquals(
            '0 2 3 5',
            $this->getCompiled('directives.loops.continue')
        );
    }

    /**
     * @dataProvider emptyProvider
     */
    public function testEmptyDirective($data, $result)
    {
        $this->assertEquals(
            $result,
            $this->getCompiled('directives.loops.empty', ['data' => $data])
        );
    }

    public function testWhileDirective()
    {
        $this->assertEquals(
            '0 1 2 3 4',
            $this->getCompiled('directives.loops.while', ['foo' => 0])
        );
    }

    public function emptyProvider()
    {
        return [
            [[], 'empty'],
            ['', 'empty'],
            [false, 'empty'],
            [true, 'not empty'],
            ['tes', 'not empty'],
        ];
    }
}
