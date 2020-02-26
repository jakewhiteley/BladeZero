<?php

namespace Bladezero\Tests\Bladezero;

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

    public function testElseCananyDirective()
    {
        $this->compiler->setCanHandler(function ($abilities, $arguments = []) {
            return \in_array($abilities, []);
        });

        $this->assertEquals(
            '',
            $this->getCompiled('auth.canany')
        );
    }
}
