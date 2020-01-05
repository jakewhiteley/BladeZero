<?php

namespace Unseenco\Blade\Tests\Unseenco;

class BladeAuthTest extends AbstractBladeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler->setCanHandler(function ($abilities, $arguments = []) {
            $allowed = ['foo'];

            return \in_array($abilities, $allowed);
        });
    }

    public function testCanDirective()
    {
        $this->assertEquals(
            'passed',
            $this->getCompiled('auth.can')
        );
    }

    public function testCannotDirective()
    {
        $this->assertEquals(
            'passed',
            $this->getCompiled('auth.cannot')
        );
    }

    public function testCananyDirective()
    {
        $this->assertEquals(
            'passed',
            $this->getCompiled('auth.canany')
        );
    }
}
