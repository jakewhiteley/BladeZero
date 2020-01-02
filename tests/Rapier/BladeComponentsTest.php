<?php

namespace Rapier\Tests\Rapier;

class BladeComponentsTest extends AbstractBladeTestCase
{
    public function testComponentDirective()
    {
        $this->assertEquals(
            '<div>foo</div>',
            $this->getCompiled('components.component')
        );
    }

    public function testComponentSlots()
    {
        $this->assertEquals(
            'title<div>foo</div>',
            $this->getCompiled('components.slots')
        );
    }

    public function testComponentSlotsWithData()
    {
        $this->assertEquals(
            'title<div>foo</div>',
            $this->getCompiled('components.slots-with-data')
        );
    }

    public function testComponentData()
    {
        $this->assertEquals(
            'title<div>foo</div>',
            $this->getCompiled('components.data')
        );
    }

    public function testComponentFirstDirective()
    {
        $this->assertEquals(
            '<div>foo</div>',
            $this->getCompiled('components.componentfirst')
        );
    }
}
