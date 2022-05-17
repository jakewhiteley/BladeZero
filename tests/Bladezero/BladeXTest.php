<?php

namespace Bladezero\Tests\Bladezero;

use Bladezero\Factory;

class BladeXTest extends AbstractBladeTestCase
{
    public function testAnonComponent(): void
    {
        $this->assertEquals(
            '<div>foo</div>',
            $this->getCompiled('bladex.anon-component')
        );
    }

    public function testAnonComponentWithAttrs(): void
    {
        $this->assertEquals(
            '<div foo="foo" bar="bar">foobar</div>',
            $this->getCompiled('bladex.anon-attrs', ['foo' => 'foo'])
        );
    }

    public function testAnonComponentWithProps(): void
    {
        $this->assertEquals(
            '<div foo="foo">foobar bar default</div>',
            $this->getCompiled('bladex.anon-attrs-with-props', ['foo' => 'foo'])
        );
    }

    public function testNestedDirectoryAnonComponent(): void
    {
        $this->assertEquals(
            'nested',
            $this->getCompiled('bladex.nested-anon-component')
        );
    }

    public function testAnonIndexes(): void
    {
        $this->assertEquals(
            'index',
            $this->getCompiled('bladex.nested-anon-index')
        );
    }

    public function testFilteredAttrs(): void
    {
        $this->assertEquals(
            '<div foo="test"> has bar </div>',
            $this->getCompiled('bladex.anon-filtered-attrs')
        );
    }

    public function testNonClassAttrMerging(): void
    {
        $this->assertEquals(
            '<div foo="foo bar" bar="bar"></div>',
            $this->getCompiled('bladex.anon-merged-attrs')
        );
    }

    public function testClassMerging(): void
    {
        $this->assertEquals(
            '<div class="foo bar"></div>',
            $this->getCompiled('bladex.anon-merged-classes')
        );
    }

    public function testScopedSlots(): void
    {
        $this->assertEquals(
            '<h1 foo="foo">title slot</h1>normal slot',
            $this->getCompiled('bladex.anon-scoped')
        );
    }

    public function testAwareDirective(): void
    {
        $this->assertEquals(
            'blueblue-greenorange',
            $this->getCompiled('bladex.anon-aware')
        );
    }

    public function testDynamicComponents(): void
    {
        $this->assertEquals(
            'title<div>Rendered as a dynamic component</div>',
            $this->getCompiled('bladex.dynamic-component', ['componentName' => 'alert', 'title' => 'title'])
        );
    }

    public function testClassBasedComponents(): void
    {
        $this->assertEquals(
            '<h1>foo</h1>default message',
            $this->getCompiled('bladex.basic-class', ['type' => 'foo'])
        );
    }

    public function testMethodsInComponentClasses(): void
    {
        $this->assertEquals(
            '<h1>foo</h1><b>default message</b><b>foobar</b>',
            $this->getCompiled('bladex.method-class', ['type' => 'foo'])
        );
    }

    public function testAccessingSlotsInComponentClasses(): void
    {
        $this->assertEquals(
            '<div class="foo"><h1>foo</h1>has a slot</div>',
            $this->getCompiled('bladex.slots-class', ['type' => 'foo'])
        );
    }
}
