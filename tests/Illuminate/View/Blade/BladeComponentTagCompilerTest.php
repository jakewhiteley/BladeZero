<?php

namespace Bladezero\Tests\Illuminate\View\Blade;


use Bladezero\Contracts\Foundation\Application;
use Bladezero\Factory;
use Bladezero\Database\Eloquent\Model;
use Bladezero\View\Compilers\BladeCompiler;
use Bladezero\View\Compilers\ComponentTagCompiler;
use Bladezero\View\Component;
use Bladezero\View\ComponentAttributeBag;
use InvalidArgumentException;
use Mockery as m;

class BladeComponentTagCompilerTest extends AbstractBladeTestCase
{
    public function testSlotsCanBeCompiled()
    {
        
        $result = $this->compiler()->compileSlots('<x-slot name="foo">
</x-slot>');

        $this->assertSame("@slot('foo', null, []) \n".' @endslot', trim($result));
    }

    public function testInlineSlotsCanBeCompiled()
    {
        
        $result = $this->compiler()->compileSlots('<x-slot:foo>
</x-slot>');

        $this->assertSame("@slot('foo', null, []) \n".' @endslot', trim($result));
    }

    public function testDynamicSlotsCanBeCompiled()
    {
        
        $result = $this->compiler()->compileSlots('<x-slot :name="$foo">
</x-slot>');

        $this->assertSame("@slot(\$foo, null, []) \n".' @endslot', trim($result));
    }

    public function testDynamicSlotsCanBeCompiledWithKeyOfObjects()
    {
        
        $result = $this->compiler()->compileSlots('<x-slot :name="$foo->name">
</x-slot>');

        $this->assertSame("@slot(\$foo->name, null, []) \n".' @endslot', trim($result));
    }

    public function testSlotsWithAttributesCanBeCompiled()
    {
        
        $result = $this->compiler()->compileSlots('<x-slot name="foo" class="font-bold">
</x-slot>');

        $this->assertSame("@slot('foo', null, ['class' => 'font-bold']) \n".' @endslot', trim($result));
    }

    public function testInlineSlotsWithAttributesCanBeCompiled()
    {
        
        $result = $this->compiler()->compileSlots('<x-slot:foo class="font-bold">
</x-slot>');

        $this->assertSame("@slot('foo', null, ['class' => 'font-bold']) \n".' @endslot', trim($result));
    }

    public function testSlotsWithDynamicAttributesCanBeCompiled()
    {
        
        $result = $this->compiler()->compileSlots('<x-slot name="foo" :class="$classes">
</x-slot>');

        $this->assertSame("@slot('foo', null, ['class' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\$classes)]) \n".' @endslot', trim($result));
    }

    public function testSlotsWithClassDirectiveCanBeCompiled()
    {
        
        $result = $this->compiler()->compileSlots('<x-slot name="foo" @class($classes)>
</x-slot>');

        $this->assertSame("@slot('foo', null, ['class' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssClasses(\$classes))]) \n".' @endslot', trim($result));
    }

    public function testSlotsWithStyleDirectiveCanBeCompiled()
    {
        
        $result = $this->compiler()->compileSlots('<x-slot name="foo" @style($styles)>
</x-slot>');

        $this->assertSame("@slot('foo', null, ['style' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssStyles(\$styles))]) \n".' @endslot', trim($result));
    }

    public function testBasicComponentParsing()
    {
        

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<div><x-alert type="foo" limit="5" @click="foo" wire:click="changePlan(\'{{ $plan }}\')" required /><x-alert /></div>');

        $this->assertSame("<div>##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['type' => 'foo','limit' => '5','@click' => 'foo','wire:click' => 'changePlan(\''.e(\$plan).'\')','required' => true]); ?>\n".
"@endComponentClass##END-COMPONENT-CLASS####BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##</div>', trim($result));
    }

    public function testBasicComponentWithEmptyAttributesParsing()
    {
        
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<div><x-alert type="" limit=\'\' @click="" required /></div>');

        $this->assertSame("<div>##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['type' => '','limit' => '','@click' => '','required' => true]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##</div>', trim($result));
    }

    public function testDataCamelCasing()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile user-id="1"></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', ['userId' => '1'])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testColonData()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile :user-id="1"></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', ['userId' => 1])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testColonDataShortSyntax()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile :$userId></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', ['userId' => \$userId])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testColonDataWithStaticClassProperty()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile :userId="User::$id"></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', ['userId' => User::\$id])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testColonDataWithStaticClassPropertyAndMultipleAttributes()
    {
        
        $result = $this->compiler(['input' => TestInputComponent::class])->compileTags('<x-input :label="Input::$label" :$name value="Joe"></x-input>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestInputComponent', 'input', ['label' => Input::\$label,'name' => \$name,'value' => 'Joe'])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestInputComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));

        $result = $this->compiler(['input' => TestInputComponent::class])->compileTags('<x-input value="Joe" :$name :label="Input::$label"></x-input>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestInputComponent', 'input', ['value' => 'Joe','name' => \$name,'label' => Input::\$label])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestInputComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testSelfClosingComponentWithColonDataShortSyntax()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile :$userId/>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', ['userId' => \$userId])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testSelfClosingComponentWithColonDataAndStaticClassPropertyShortSyntax()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile :userId="User::$id"/>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', ['userId' => User::\$id])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testSelfClosingComponentWithColonDataMultipleAttributesAndStaticClassPropertyShortSyntax()
    {
        
        $result = $this->compiler(['input' => TestInputComponent::class])->compileTags('<x-input :label="Input::$label" value="Joe" :$name />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestInputComponent', 'input', ['label' => Input::\$label,'value' => 'Joe','name' => \$name])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestInputComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));

        $result = $this->compiler(['input' => TestInputComponent::class])->compileTags('<x-input :$name :label="Input::$label" value="Joe" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestInputComponent', 'input', ['name' => \$name,'label' => Input::\$label,'value' => 'Joe'])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestInputComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testEscapedColonAttribute()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile :user-id="1" ::title="user.name"></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', ['userId' => 1])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([':title' => 'user.name']); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testColonAttributesIsEscapedIfStrings()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile :src="\'foo\'"></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['src' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute('foo')]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testClassDirective()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile @class(["bar"=>true])></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['class' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssClasses(['bar'=>true]))]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testStyleDirective()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile @style(["bar"=>true])></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['style' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssStyles(['bar'=>true]))]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testColonNestedComponentParsing()
    {
        
        $result = $this->compiler(['foo:alert' => TestAlertComponent::class])->compileTags('<x-foo:alert></x-foo:alert>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'foo:alert', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testColonStartingNestedComponentParsing()
    {
        
        $result = $this->compiler(['foo:alert' => TestAlertComponent::class])->compileTags('<x:foo:alert></x-foo:alert>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'foo:alert', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testSelfClosingComponentsCanBeCompiled()
    {
        
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<div><x-alert/></div>');

        $this->assertSame("<div>##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##</div>', trim($result));
    }

    public function testClassNamesCanBeGuessed()
    {
        
        $container->instance(Application::class, $app = m::mock(Application::class));
        $app->shouldReceive('getNamespace')->once()->andReturn('App\\');
        

        $result = $this->compiler()->guessClassName('alert');

        $this->assertSame("\Bladezero\Tests\Bladezero\Components\Alert", trim($result));

        
    }

    public function testClassNamesCanBeGuessedWithNamespaces()
    {
        
        $container->instance(Application::class, $app = m::mock(Application::class));
        $app->shouldReceive('getNamespace')->once()->andReturn('App\\');
        

        $result = $this->compiler()->guessClassName('base.alert');

        $this->assertSame("\Bladezero\Tests\Bladezero\Components\Base\Alert", trim($result));

        
    }

    public function testComponentsCanBeCompiledWithHyphenAttributes()
    {
        

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<x-alert class="bar" wire:model="foo" x-on:click="bar" @click="baz" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['class' => 'bar','wire:model' => 'foo','x-on:click' => 'bar','@click' => 'baz']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testSelfClosingComponentsCanBeCompiledWithDataAndAttributes()
    {
        
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<x-alert title="foo" class="bar" wire:model="foo" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', ['title' => 'foo'])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['class' => 'bar','wire:model' => 'foo']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testComponentCanReceiveAttributeBag()
    {
        

        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile class="bar" {{ $attributes }} wire:model="foo"></x-profile>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['class' => 'bar','attributes' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\$attributes),'wire:model' => 'foo']); ?> @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testSelfClosingComponentCanReceiveAttributeBag()
    {
        

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<div><x-alert title="foo" class="bar" {{ $attributes->merge([\'class\' => \'test\']) }} wire:model="foo" /></div>');

        $this->assertSame("<div>##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', ['title' => 'foo'])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['class' => 'bar','attributes' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\$attributes->merge(['class' => 'test'])),'wire:model' => 'foo']); ?>\n".
            '@endComponentClass##END-COMPONENT-CLASS##</div>', trim($result));
    }

    public function testComponentsCanHaveAttachedWord()
    {
        
        $result = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile></x-profile>Words');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', 'profile', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##Words", trim($result));
    }

    public function testSelfClosingComponentsCanHaveAttachedWord()
    {
        
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<x-alert/>Words');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##Words', trim($result));
    }

    public function testSelfClosingComponentsCanBeCompiledWithBoundData()
    {
        
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<x-alert :title="$title" class="bar" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', ['title' => \$title])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['class' => 'bar']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testPairedComponentTags()
    {
        
        $result = $this->compiler(['alert' => TestAlertComponent::class])->compileTags('<x-alert>
</x-alert>');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', 'alert', [])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?>
 @endComponentClass##END-COMPONENT-CLASS##", trim($result));
    }

    public function testClasslessComponents()
    {
        
        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));
        $app->shouldReceive('getNamespace')->once()->andReturn('App\\');
        $factory->shouldReceive('exists')->once()->andReturn(true);
        

        $result = $this->compiler()->compileTags('<x-anonymous-component :name="\'Taylor\'" :age="31" wire:model="foo" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\View\AnonymousComponent', 'anonymous-component', ['view' => 'components.anonymous-component','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\View\AnonymousComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['name' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testClasslessComponentsWithIndexView()
    {
        
        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));
        
        $result = $this->compiler()->compileTags('<x-anonymous-component-index :name="\'Taylor\'" :age="31" wire:model="foo" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\View\AnonymousComponent', 'anonymous-component-index', ['view' => 'components.anonymous-component-index.index','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\View\AnonymousComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['name' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testPackagesClasslessComponents()
    {
        $this->markTestSkipped('We do not suport packages');
        
        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));
        
        
        

        $result = $this->compiler()->compileTags('<x-package::anonymous-component :name="\'Taylor\'" :age="31" wire:model="foo" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\View\AnonymousComponent', 'package::anonymous-component', ['view' => 'package::components.anonymous-component','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\View\AnonymousComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['name' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>\n".
'@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testClasslessComponentsWithAnonymousComponentNamespace()
    {
        

        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));

        $app->shouldReceive('getNamespace')->once()->andReturn('App\\');
        $factory->shouldReceive('exists')->times(3)->andReturnUsing(function ($arg) {
            // In our test, we'll do as if the 'public.frontend.anonymous-component'
            // view exists and not the others.
            return $arg === 'public.frontend.anonymous-component';
        });

        

        $blade = m::mock(BladeCompiler::class)->makePartial();

        $blade->shouldReceive('getAnonymousComponentNamespaces')->once()->andReturn([
            'frontend' => 'public.frontend',
        ]);

        $compiler = $this->compiler([], [], $blade);

        $result = $compiler->compileTags('<x-frontend::anonymous-component :name="\'Taylor\'" :age="31" wire:model="foo" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\View\AnonymousComponent', 'frontend::anonymous-component', ['view' => 'public.frontend.anonymous-component','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\View\AnonymousComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['name' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>\n".
            '@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testClasslessComponentsWithAnonymousComponentNamespaceWithIndexView()
    {
        

        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));

        $app->shouldReceive('getNamespace')->once()->andReturn('App\\');
        $factory->shouldReceive('exists')->times(4)->andReturnUsing(function (string $viewNameBeingCheckedForExistence) {
            // In our test, we'll do as if the 'public.frontend.anonymous-component'
            // view exists and not the others.
            return $viewNameBeingCheckedForExistence === 'admin.auth.components.anonymous-component.index';
        });

        

        $blade = m::mock(BladeCompiler::class)->makePartial();

        $blade->shouldReceive('getAnonymousComponentNamespaces')->once()->andReturn([
            'admin.auth' => 'admin.auth.components',
        ]);

        $compiler = $this->compiler([], [], $blade);

        $result = $compiler->compileTags('<x-admin.auth::anonymous-component :name="\'Taylor\'" :age="31" wire:model="foo" />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\View\AnonymousComponent', 'admin.auth::anonymous-component', ['view' => 'admin.auth.components.anonymous-component.index','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\View\AnonymousComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes(['name' => \Bladezero\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>\n".
            '@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testClasslessComponentsWithAnonymousComponentPath()
    {
        

        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));

        $app->shouldReceive('getNamespace')->once()->andReturn('App\\');

        $factory->shouldReceive('exists')->andReturnUsing(function ($arg) {
            return $arg === md5('test-directory').'::panel.index';
        });

        

        $blade = m::mock(BladeCompiler::class)->makePartial();

        $blade->shouldReceive('getAnonymousComponentPaths')->once()->andReturn([
            ['path' => 'test-directory', 'prefix' => null, 'prefixHash' => md5('test-directory')],
        ]);

        $compiler = $this->compiler([], [], $blade);

        $result = $compiler->compileTags('<x-panel />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\View\AnonymousComponent', 'panel', ['view' => '".md5('test-directory')."::panel.index','data' => []])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\View\AnonymousComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?>\n".
            '@endComponentClass##END-COMPONENT-CLASS##', trim($result));
    }

    public function testClasslessIndexComponentsWithAnonymousComponentPath()
    {
        

        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));

        $app->shouldReceive('getNamespace')->once()->andReturn('App\\');

        $factory->shouldReceive('exists')->andReturnUsing(function ($arg) {
            return $arg === md5('test-directory').'::panel';
        });

        

        $blade = m::mock(BladeCompiler::class)->makePartial();

        $blade->shouldReceive('getAnonymousComponentPaths')->once()->andReturn([
            ['path' => 'test-directory', 'prefix' => null, 'prefixHash' => md5('test-directory')],
        ]);

        $compiler = $this->compiler([], [], $blade);

        $result = $compiler->compileTags('<x-panel />');

        $this->assertSame("##BEGIN-COMPONENT-CLASS##@component('Bladezero\View\AnonymousComponent', 'panel', ['view' => '".md5('test-directory')."::panel','data' => []])
<?php if (isset(\$attributes) && \$attributes instanceof Bladezero\View\ComponentAttributeBag && \$constructor = (new ReflectionClass(Bladezero\View\AnonymousComponent::class))->getConstructor()): ?>
<?php \$attributes = \$attributes->except(collect(\$constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php \$component->withAttributes([]); ?>\n".
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
        $this->mockViewFactory(false);

        $this->expectException(InvalidArgumentException::class);

        $this->compiler(['alert' => 'foo.bar'])->compileTags('<x-alert />');
    }

    public function testItThrowsAnExceptionForNonExistingClass()
    {
        $this->markTestSkipped();
        
        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));
        $app->shouldReceive('getNamespace')->once()->andReturn('App\\');
        $factory->shouldReceive('exists')->twice()->andReturn(false);
        

        $this->expectException(InvalidArgumentException::class);

        $this->compiler()->compileTags('<x-alert />');
    }

    public function testAttributesTreatedAsPropsAreRemovedFromFinalAttributes()
    {
        
        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));
        $container->alias(Factory::class, 'view');
        $app->shouldReceive('getNamespace')->never()->andReturn('App\\');
        $factory->shouldReceive('exists')->never();

        

        $attributes = new ComponentAttributeBag(['userId' => 'bar', 'other' => 'ok']);

        $component = m::mock(Component::class);
        $component->shouldReceive('withName')->with('profile')->once();
        $component->shouldReceive('shouldRender')->once()->andReturn(true);
        $component->shouldReceive('resolveView')->once()->andReturn('');
        $component->shouldReceive('data')->once()->andReturn([]);
        $component->shouldReceive('withAttributes')->once();

        Component::resolveComponentsUsing(fn () => $component);

        $__env = m::mock(\Bladezero\Factory::class);
        $__env->shouldReceive('startComponent')->once();
        $__env->shouldReceive('renderComponent')->once();

        $template = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile {{ $attributes }} />');
        $template = $this->compiler->compileString($template);

        ob_start();
        eval(" ?> $template <?php ");
        ob_get_clean();

        $this->assertNull($attributes->get('userId'));
        $this->assertSame($attributes->get('other'), 'ok');
    }

    protected function mockViewFactory($existsSucceeds = true)
    {
        
        $container->instance(Factory::class, $factory = m::mock(Factory::class));
        $container->alias(Factory::class, 'view');
        $factory->shouldReceive('exists')->andReturn($existsSucceeds);
        
    }

    protected function compiler(array $aliases = [], array $namespaces = [], ?BladeCompiler $blade = null)
    {
        $factory = new Factory(__DIR__ . '../../../../Bladezero/fixtures/files', __DIR__ . '../../../../Bladezero/fixtures/cache');
return new ComponentTagCompiler(
            $aliases, $namespaces, $blade
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

class TestInputComponent extends Component
{
    public $userId;

    public function __construct($name, $label, $value)
    {
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
    }

    public function render()
    {
        return 'input';
    }
}
