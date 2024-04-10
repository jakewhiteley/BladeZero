<?php

namespace Bladezero\Tests\Illuminate\View\Blade;


use Bladezero\Contracts\Foundation\Application;
use Bladezero\Factory;
use Bladezero\Database\Eloquent\Model;
use Bladezero\View\Compilers\BladeCompiler;
use Bladezero\View\Compilers\ComponentTagCompiler;
use Bladezero\View\Component;
use InvalidArgumentException;
use Mockery;

class BladeComponentTagCompilerTest extends AbstractBladeTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testSlotsCanBeCompiled()
    {
        $result = $this->compiler()->compileSlots('<x-slot name="foo">
</x-slot>');

        $this->assertSame("@slot('foo', null, []) \n".' @endslot', trim($result));
    }

    public function testDynamicSlotsCanBeCompiled()
    {
        $result = $this->compiler()->compileSlots('<x-slot :name="$foo">
</x-slot>');

        $this->assertSame("@slot(\$foo, null, []) \n".' @endslot', trim($result));
    }

    public function testSlotsWithAttributesCanBeCompiled()
    {
        $result = $this->compiler()->compileSlots('<x-slot name="foo" class="font-bold">
</x-slot>');

        $this->assertSame("@slot('foo', null, ['class' => 'font-bold']) \n".' @endslot', trim($result));
    }

    public function testSlotsWithDynamicAttributesCanBeCompiled()
    {
        $result = $this->compiler()->compileSlots('<x-slot name="foo" :class="$classes">
</x-slot>');

        $this->assertSame("@slot('foo', null, ['class' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\$classes)]) \n".' @endslot', trim($result));
    }

    public function testBasicComponentParsing()
    {
        

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<div><x-alert type="foo" limit="5" @click="foo" wire:click="changePlan(\'{{ $plan }}\')" required /><x-alert /></div>');

        $this->assertSame("<div>##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php \$component->withAttributes(['type' => 'foo','limit' => '5','@click' => 'foo','wire:click' => 'changePlan(\''.e(\$plan).'\')','required' => true]); ?>\n".
"@endComponentClass##END-COMPONENT-CLASS####BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php \$component->withAttributes([]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##</div>', trim($result));
    }

    public function testBasicComponentWithEmptyAttributesParsing()
    {
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<div><x-alert type="" limit=\'\' @click="" required /></div>');

        $this->assertSame("<div>##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php \$component->withAttributes(['type' => '','limit' => '','@click' => '','required' => true]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##</div>', trim($result));
    }

    public function testDataCamelCasing()
    {
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile user-id="1"></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', ['userId' => '1'])
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testColonData()
    {
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile :user-id="1"></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', ['userId' => 1])
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testEscapedColonAttribute()
    {
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile :user-id="1" ::title="user.name"></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', ['userId' => 1])
<?php \$component->withAttributes([':title' => 'user.name']); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testColonAttributesIsEscapedIfStrings()
    {
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile :src="\'foo\'"></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', [])
<?php \$component->withAttributes(['src' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute('foo')]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testColonNestedComponentParsing()
    {
        $result = $this->compiler(['foo:alert' => TestAlertComponent::class])->compileTags('<x-foo:alert></x-foo:alert>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'foo:alert', [])
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testColonStartingNestedComponentParsing()
    {
        $result = $this->compiler(['foo:alert' => TestAlertComponent::class])->compileTags('<x:foo:alert></x-foo:alert>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'foo:alert', [])
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testSelfClosingComponentsCanBeCompiled()
    {
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<div><x-alert/></div>');

        $this->assertSame("<div>##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php \$component->withAttributes([]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##</div>', trim($result));
    }

    public function testClassNamesCanBeGuessed()
    {
        
        
        
        

        $result = $this->compiler()->guessClassName('alert');

        $this->assertSame("\Bladezero\Tests\Bladezero\Components\Alert", trim($result));

        
    }

    public function testClassNamesCanBeGuessedWithNamespaces()
    {
        
        
        
        

        $result = $this->compiler()->guessClassName('base.alert');

        $this->assertSame("\Bladezero\Tests\Bladezero\Components\Base\Alert", trim($result));

        
    }

    public function testComponentsCanBeCompiledWithHyphenAttributes()
    {
        

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<x-alert class="bar" wire:model="foo" x-on:click="bar" @click="baz" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php \$component->withAttributes(['class' => 'bar','wire:model' => 'foo','x-on:click' => 'bar','@click' => 'baz']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testSelfClosingComponentsCanBeCompiledWithDataAndAttributes()
    {
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<x-alert title="foo" class="bar" wire:model="foo" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', ['title' => 'foo'])
<?php \$component->withAttributes(['class' => 'bar','wire:model' => 'foo']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testComponentCanReceiveAttributeBag()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile class="bar" {{ $attributes }} wire:model="foo"></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', [])
<?php \$component->withAttributes(['class' => 'bar','attributes' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\$attributes),'wire:model' => 'foo']); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testSelfClosingComponentCanReceiveAttributeBag()
    {
        

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<div><x-alert title="foo" class="bar" {{ $attributes->merge([\'class\' => \'test\']) }} wire:model="foo" /></div>');

        $this->assertSame("<div>##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', ['title' => 'foo'])
<?php \$component->withAttributes(['class' => 'bar','attributes' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\$attributes->merge(['class' => 'test'])),'wire:model' => 'foo']); ?>\n".
            '@endComponentClass##END-COMPONENT-CLASS##</div>', trim($result));
    }

    public function testComponentsCanHaveAttachedWord()
    {
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile></x-profile>Words');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', [])
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##Words", trim($result));
    }

    public function testSelfClosingComponentsCanHaveAttachedWord()
    {
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<x-alert/>Words');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php \$component->withAttributes([]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##Words', trim($result));
    }

    public function testSelfClosingComponentsCanBeCompiledWithBoundData()
    {
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<x-alert :title="$title" class="bar" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', ['title' => \$title])
<?php \$component->withAttributes(['class' => 'bar']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testPairedComponentTags()
    {
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<x-alert>
</x-alert>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php \$component->withAttributes([]); ?>
 @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testClasslessComponents()
    {
        
        
        
        
        
        

        $result = $this->compiler()->compileTags('<x-anonymous-component :name="\'Taylor\'" :age="31" wire:model="foo" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\View\AnonymousComponent', 'anonymous-component', ['view' => 'components.anonymous-component','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php \$component->withAttributes(['name' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testClasslessComponentsWithIndexView()
    {
        
        
        
        
        $result = $this->compiler()->compileTags('<x-anonymous-component-index :name="\'Taylor\'" :age="31" wire:model="foo" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\View\AnonymousComponent', 'anonymous-component-index', ['view' => 'components.anonymous-component-index.index','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php \$component->withAttributes(['name' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testPackagesClasslessComponents()
    {
        $this->markTestSkipped('We do not suport packages');
        
        
        
        
        
        

        $result = $this->compiler()->compileTags('<x-package::anonymous-component :name="\'Taylor\'" :age="31" wire:model="foo" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\View\AnonymousComponent', 'package::anonymous-component', ['view' => 'package::components.anonymous-component','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php \$component->withAttributes(['name' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testAttributeSanitization()
    {
        $class = new class
        {
            public function __toString()
            {
                return '<hi>';
            }
        };

        

        $this->assertEquals(e('<hi>'), BladeCompiler::sanitizeComponentAttribute('<hi>'));
        $this->assertEquals(e('1'), BladeCompiler::sanitizeComponentAttribute('1'));
        $this->assertEquals(1, BladeCompiler::sanitizeComponentAttribute(1));
        $this->assertEquals(e('<hi>'), BladeCompiler::sanitizeComponentAttribute($class));
        
    }

    public function testItThrowsAnExceptionForNonExistingAliases()
    {
        
        
        
        
        
        

        $this->expectException(InvalidArgumentException::class);

        $this->compiler(['alert' => 'foo.bar'])->compileTags('<x-alert />');
    }

    public function testItThrowsAnExceptionForNonExistingClass()
    {
        $this->markTestSkipped();
        
        
        
        
        
        

        $this->expectException(InvalidArgumentException::class);

        $this->compiler()->compileTags('<x-alert />');
    }

    protected function mockViewFactory($existsSucceeds = true)
    {
        
        
        $factory->shouldReceive('exists')->andReturn($existsSucceeds);
        
    }

    protected function compiler($aliases = [])
    {
        $factory = new Factory(__DIR__ . '../../../../Bladezero/fixtures/files', __DIR__ . '../../../../Bladezero/fixtures/cache');
return new ComponentTagCompiler(
            $aliases
        );
    }
}

class TestAlertComponent extends Component
{
    public $title;

    public function __construct($title = 'foo', $userId = 1)
    {
        $this->title = $title;
    }

    public function render()
    {
        return 'alert';
    }
}

class TestProfileComponent extends Component
{
    public $userId;

    public function __construct($userId = 'foo')
    {
        $this->userId = $userId;
    }

    public function render()
    {
        return 'profile';
    }
}
