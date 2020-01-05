<?php

namespace Unseenco\Blade\Tests\Unseenco;

class BladeGeneralTest extends AbstractBladeTestCase
{
    public function testVerbatimDirective()
    {
        $this->assertEquals(
            '<div class="container"> Hello, {{ name }}. </div>',
            $this->getCompiled('general.verbatim')
        );
    }

    public function testUnescapedEcho()
    {
        $string = '<div>&amp;</div>';

        $this->assertEquals(
            '&lt;div&gt;&amp;amp;&lt;/div&gt;--<div>&amp;</div>',
            $this->getCompiled('general.unescaped', compact('string'))
        );
    }
}
