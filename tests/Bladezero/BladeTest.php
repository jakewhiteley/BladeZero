<?php

namespace Bladezero\Tests\Bladezero;

use InvalidArgumentException;

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
            dirname(__FILE__) . '/fixtures/files/conditionals'
        );

        $this->assertTrue($this->compiler->exists('Conditionals::isset'));

        $this->compiler->prependNamespace(
            'json',
            dirname(__FILE__) . '/fixtures/files/json'
        );

        $this->assertTrue($this->compiler->exists('json::json'));

        $this->compiler->replaceNamespace(
            'Conditionals',
            dirname(__FILE__) . '/fixtures/files/json'
        );

        $this->compiler->flushFinderCache();

        $this->assertFalse($this->compiler->exists('Conditionals::isset'));
    }

    public function testCustomIfStatements()
    {
        $this->compiler->if('env', function($env) {
            return $env === 'test';
        });

        $this->assertEquals(
            'is testing not production',
            $this->getCompiled('general.customif')
        );
    }

    public function testComponentAliasing()
    {
        $this->compiler->component('components.alert', 'alert');

        $this->assertEquals(
            'title<div>foo</div>',
            $this->getCompiled('general.components')
        );
    }

    public function testCustomDirectives()
    {
        $this->compiler->directive('foo', function($expression) {
            return "<?php echo 'foo' . $expression; ?>";
        });

        $this->assertEquals(
            'foobar',
            $this->getCompiled('general.directives')
        );
    }

    public function testIncludeAliasing()
    {
        $this->compiler->include('php.raw', 'foo');

        $this->assertEquals(
            'foo',
            $this->getCompiled('general.include')
        );
    }

    public function testDefaultCanHandler()
    {
        $this->assertEquals(
            'failed',
            $this->getCompiled('auth.cannot')
        );
    }

    public function testAuthCanHandler()
    {
        $this->assertEquals(
            'guest guest',
            $this->getCompiled('conditionals.auth')
        );
    }

    public function testDefaultErrorHandler()
    {
        $this->assertEquals(
            '',
            $this->getCompiled('errors.default')
        );
    }

    public function testCustomErrorHandler()
    {
        $this->compiler->setErrorHandler(function(string $error) {
            if ($error == 'foo') {
                return 'foo $message';
            }

            return false;
        });

        $this->assertEquals(
            'foo $message',
            $this->getCompiled('errors.default')
        );
    }

    public function testPhpFilesCanBeRendered()
    {
        $this->assertEquals(
            'php file',
            $this->getCompiled('general.foo')
        );
    }

    public function testHtmlFilesCanBeRendered()
    {
        $this->assertEquals(
            'HTML file',
            $this->getCompiled('general.html')
        );
    }

    public function testExceptionThrownIfUnknownExtensionRendered()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getCompiled('general.xml');
    }

    /**
     * @depends testExceptionThrownIfUnknownExtensionRendered
     */
    public function testExceptionThrownIfExtensionNotAddedYet()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->compiler->getEngineFromPath(dirname(__FILE__) . '/fixtures/files/general/xml.xml');
    }

    /**
     * @depends testExceptionThrownIfExtensionNotAddedYet
     */
    public function testNewExtensionsCanBeAdded()
    {
        $this->compiler->addExtension('xml', 'file');

        $this->assertEquals(
            '<foo>bar</foo>',
            $this->getCompiled('general.xml')
        );

        $this->assertArrayHasKey('xml', $this->compiler->getExtensions());
    }

    public function testShare()
    {
        $this->assertNull($this->compiler->shared('foo'));
        $this->compiler->share('foo', 'bar');
        $this->assertEquals('bar', $this->compiler->shared('foo'));
    }
}
