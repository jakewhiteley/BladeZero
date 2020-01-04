<?php

namespace Unseenco\Blade\Tests\Unseenco\Blade;

class BladeJsonTest extends AbstractBladeTestCase
{
    public function testJsonDirective()
    {
        $data = [
            'foo' => 'bar'
        ];

        $this->assertEquals(
            '{"foo":"bar"}',
            $this->getCompiled('json.json', compact('data'))
        );
    }
}
