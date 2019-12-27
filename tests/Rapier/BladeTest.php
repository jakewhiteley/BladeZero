<?php

namespace Rapier\Tests\Rapier;

class BladeTest extends AbstractBladeTestCase
{
    public function testLocationsCanBeAdded()
    {
        $this->assertFalse($this->compiler->exists('partial'));
        $this->compiler->addLocation(dirname(__FILE__) . '/fixtures/partials');
        $this->assertTrue($this->compiler->exists('partial'));
    }

    public function testNamespacesCanBeAdded()
    {
        $this->assertFalse($this->compiler->exists('Conditionals::isset'));

        $this->compiler->addNamespace(
            'Conditionals',
            dirname(__FILE__) . '/fixtures/files/directives/conditionals'
        );

        $this->assertTrue($this->compiler->exists('Conditionals::isset'));

        $this->compiler->prependNamespace(
            'json',
            dirname(__FILE__) . '/fixtures/files/directives/json'
        );

        $this->assertTrue($this->compiler->exists('json::json'));

        $this->compiler->replaceNamespace(
            'Conditionals',
            dirname(__FILE__) . '/fixtures/files/directives/json'
        );

        $this->compiler->flushFinderCache();

        $this->assertFalse($this->compiler->exists('Conditionals::isset'));
    }
}
