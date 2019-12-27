<?php

namespace Rapier\Tests\Rapier;

class BladeJsonTest extends AbstractBladeTestCase
{
    public function testJsonDirective()
    {
        $data = [
            'foo' => 'bar'
        ];

        $this->assertEquals(
            '{"foo":"bar"}',
            $this->getCompiled('directives.json.json', compact('data'))
        );
    }
}
