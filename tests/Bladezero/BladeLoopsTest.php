<?php

namespace Bladezero\Tests\Bladezero;

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
            $this->getCompiled('loops.foreach', compact('data'))
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
            $this->getCompiled('loops.forelse', compact('users'))
        );

        $this->assertEquals(
            'No users',
            $this->getCompiled('loops.forelse', ['users' => []])
        );
    }

    public function testForDirective()
    {
        $this->assertEquals(
            '0 1 2',
            $this->getCompiled('loops.for')
        );
    }

    public function testContinueDirective()
    {
        $this->assertEquals(
            '0 2 3 5',
            $this->getCompiled('loops.continue')
        );
    }

    /**
     * @dataProvider emptyProvider
     */
    public function testEmptyDirective($data, $result)
    {
        $this->assertEquals(
            $result,
            $this->getCompiled('loops.empty', ['data' => $data])
        );
    }

    public function testWhileDirective()
    {
        $this->assertEquals(
            '0 1 2 3 4',
            $this->getCompiled('loops.while', ['foo' => 0])
        );
    }

    public function testLoopVariable()
    {
        $this->assertEquals(
            '10 2 1 odd first 10 2 1 even last',
            $this->getCompiled('loops.loop', ['data' => ['a', 'b']])
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
