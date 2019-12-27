<?php

namespace Rapier\Tests\Rapier;

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
            $this->getCompiled('directives.conditionals.if', ['foo' => true])
        );

        $this->assertEquals(
            'passed',
            $this->getCompiled('directives.conditionals.else', ['foo' => false])
        );

        $this->assertEmpty($this->getCompiled('directives.conditionals.if', ['foo' => false]));
    }

    public function testElseIfDirective()
    {
        $this->assertEquals(
            'foo',
            $this->getCompiled('directives.conditionals.elseif', ['foo' => true])
        );

        $this->assertEquals(
            'bar',
            $this->getCompiled('directives.conditionals.elseif', ['bar' => true])
        );

        $this->assertEmpty($this->getCompiled('directives.conditionals.elseif', []));
    }

    public function testSwitchDirectives()
    {
        $this->assertEquals(
            'the var was foo',
            $this->getCompiled('directives.conditionals.switch', ['foo' => 'foo'])
        );

        $this->assertEquals(
            'the var was bar',
            $this->getCompiled('directives.conditionals.switch', ['foo' => 'bar'])
        );

        $this->assertEquals(
            'the var was neither foo nor bar',
            $this->getCompiled('directives.conditionals.switch', ['foo' => ''])
        );
    }

    public function testUnlessDirectives()
    {
        $this->assertEquals(
            'passed',
            $this->getCompiled('directives.conditionals.unless', ['foo' => false])
        );

        $this->assertEquals(
            'failed',
            $this->getCompiled('directives.conditionals.unless', ['foo' => true])
        );
    }

    public function testIssetDirectives()
    {
        $this->assertEquals(
            'passed',
            $this->getCompiled('directives.conditionals.isset', ['foo' => 'foo'])
        );

        $this->assertEquals(
            '',
            $this->getCompiled('directives.conditionals.isset', [])
        );

        $this->assertEquals(
            '',
            $this->getCompiled('directives.conditionals.isset', ['foo' => null])
        );
    }

    public function testHassectionDirective()
    {
        $this->assertEquals(
            'has foo passed',
            $this->getCompiled('directives.conditionals.hassection')
        );
    }

    public function testAuthDirective()
    {
        $this->assertEquals(
            'logged in logged in',
            $this->getCompiled('directives.conditionals.auth')
        );
    }

    public function testAuthDirectiveWithParams()
    {
        $this->assertEquals(
            'logged in is admin',
            $this->getCompiled('directives.conditionals.authwithparams')
        );
    }
}
