<?php

namespace Bladezero\Tests\Bladezero;

class BladeConditionalsTest extends AbstractBladeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler->setAuthHandler(function ($role = null) {
            $currentRole = 'admin';
            return $role === null || $currentRole === $role;
        });
    }

    public function testIfDirective()
    {
        $this->assertEquals(
            'passed',
            $this->getCompiled('conditionals.if', ['foo' => true])
        );

        $this->assertEquals(
            'passed',
            $this->getCompiled('conditionals.else', ['foo' => false])
        );

        $this->assertEmpty($this->getCompiled('conditionals.if', ['foo' => false]));
    }

    public function testElseIfDirective()
    {
        $this->assertEquals(
            'foo',
            $this->getCompiled('conditionals.elseif', ['foo' => true])
        );

        $this->assertEquals(
            'bar',
            $this->getCompiled('conditionals.elseif', ['bar' => true])
        );

        $this->assertEmpty($this->getCompiled('conditionals.elseif', []));
    }

    public function testSwitchDirectives()
    {
        $this->assertEquals(
            'the var was foo',
            $this->getCompiled('conditionals.switch', ['foo' => 'foo'])
        );

        $this->assertEquals(
            'the var was bar',
            $this->getCompiled('conditionals.switch', ['foo' => 'bar'])
        );

        $this->assertEquals(
            'the var was neither foo nor bar',
            $this->getCompiled('conditionals.switch', ['foo' => ''])
        );
    }

    public function testUnlessDirectives()
    {
        $this->assertEquals(
            'passed',
            $this->getCompiled('conditionals.unless', ['foo' => false])
        );

        $this->assertEquals(
            'failed',
            $this->getCompiled('conditionals.unless', ['foo' => true])
        );
    }

    public function testIssetDirectives()
    {
        $this->assertEquals(
            'passed',
            $this->getCompiled('conditionals.isset', ['foo' => 'foo'])
        );

        $this->assertEquals(
            '',
            $this->getCompiled('conditionals.isset', [])
        );

        $this->assertEquals(
            '',
            $this->getCompiled('conditionals.isset', ['foo' => null])
        );
    }

    public function testHassectionDirective()
    {
        $this->assertEquals(
            'has foo passed',
            $this->getCompiled('conditionals.hassection')
        );
    }

    public function testAuthDirective()
    {
        $this->assertEquals(
            'logged in logged in',
            $this->getCompiled('conditionals.auth')
        );
    }

    public function testAuthDirectiveWithParams()
    {
        $this->assertEquals(
            'logged in is admin',
            $this->getCompiled('conditionals.authwithparams')
        );
    }
}
