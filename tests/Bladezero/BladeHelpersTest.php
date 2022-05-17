<?php

namespace Bladezero\Tests\Bladezero;

class BladeHelpersTest extends AbstractBladeTestCase
{
    public function testDefaultCsrfDirective()
    {
        $this->assertMatchesRegularExpression(
            '<input type="hidden" name="_token" value="[a-zA-Z0-9]{40,}">',
            $this->getCompiled('helpers.csrf')
        );
    }

    public function testCsrfDirective()
    {
        $this->compiler->setCsrfHandler(function() {
            return 'works';
        });

        $this->assertEquals(
            '<input type="hidden" name="_token" value="works">',
            $this->getCompiled('helpers.csrf')
        );
    }

    public function testMethodDirective()
    {
        $this->assertEquals(
            '<input type="hidden" name="_method" value="post">',
            $this->getCompiled('helpers.method')
        );
    }
}
