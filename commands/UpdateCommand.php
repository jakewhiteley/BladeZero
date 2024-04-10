<?php

namespace Bladezero\Commands;

use Bladezero\Tests\Illuminate\Support\StringableObjectStub;
use Curl\Curl;
use Curl\MultiCurl;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;

class UpdateCommand extends Command
{
    const REPO_BASE = 'https://github.com/laravel/framework/tree/';

    const RAW_BASE = 'https://raw.githubusercontent.com/laravel/framework/';

    const TEST_FILES = [
        '/Support/SupportStrTest.php',
        '/View/ViewFileViewFinderTest.php',
        '/View/ViewEngineResolverTest.php',
        '/Support/SupportPluralizerTest.php',
        '/Support/SupportStringableTest.php',
    ];

    const SUPPORT_FILES = [
        '/Support/Str.php',
        '/Support/Pluralizer.php',
        '/Support/HigherOrderTapProxy.php',
        '/Support/Stringable.php',
        '/Support/HtmlString.php',
        '/Support/MessageBag.php',
        '/Support/ViewErrorBag.php',
        '/Support/Optional.php',
        '/Collections/Enumerable.php',
        '/Support/Reflector.php',
        '/Support/Traits/Conditionable.php',
        '/Support/Traits/ReflectsClosures.php',
        '/Support/Traits/Tappable.php',
    ];

    const VIEW_FILES = [
        '/View/Compilers/BladeCompiler.php',
        '/View/Compilers/Compiler.php',
        '/View/Compilers/ComponentTagCompiler.php',
        '/View/Compilers/CompilerInterface.php',
        '/View/Compilers/Concerns/CompilesAuthorizations.php',
        '/View/Compilers/Concerns/CompilesComments.php',
        '/View/Compilers/Concerns/CompilesAuthorizations.php',
        '/View/Compilers/Concerns/CompilesClasses.php',
        '/View/Compilers/Concerns/CompilesConditionals.php',
        '/View/Compilers/Concerns/CompilesEchos.php',
        '/View/Compilers/Concerns/CompilesErrors.php',
        '/View/Compilers/Concerns/CompilesHelpers.php',
        '/View/Compilers/Concerns/CompilesIncludes.php',
        '/View/Compilers/Concerns/CompilesInjections.php',
        '/View/Compilers/Concerns/CompilesJson.php',
        '/View/Compilers/Concerns/CompilesJs.php',
        '/View/Compilers/Concerns/CompilesLayouts.php',
        '/View/Compilers/Concerns/CompilesLoops.php',
        '/View/Compilers/Concerns/CompilesRawPhp.php',
        '/View/Compilers/Concerns/CompilesStacks.php',
        '/View/Compilers/Concerns/CompilesTranslations.php',
        '/View/Compilers/Concerns/CompilesComponents.php',
        '/View/Concerns/ManagesComponents.php',
        '/View/Concerns/ManagesEvents.php',
        '/View/Concerns/ManagesLayouts.php',
        '/View/Concerns/ManagesLoops.php',
        '/View/Concerns/ManagesStacks.php',
        '/View/Concerns/ManagesTranslations.php',
        '/View/Concerns/ManagesComponents.php',
        '/View/Engines/CompilerEngine.php',
        '/View/Engines/Engine.php',
        '/View/Engines/EngineResolver.php',
        '/View/Engines/FileEngine.php',
        '/View/Engines/PhpEngine.php',
        '/View/Compilers/ComponentTagCompiler.php',
        '/View/AppendableAttributeValue.php',
        '/View/Component.php',
        '/View/DynamicComponent.php',
        '/View/ComponentSlot.php',
        '/View/AnonymousComponent.php',
        '/View/InvokableComponentVariable.php',
        '/View/ComponentAttributeBag.php',
        '/View/ViewException.php',
        '/View/View.php',
        '/Contracts/View/View.php',
        '/Contracts/Support/MessageProvider.php',
        '/Contracts/Support/DeferringDisplayableValue.php',
        '/Contracts/Support/Renderable.php',
        '/Contracts/Support/Jsonable.php',
        '/Contracts/Support/MessageBag.php',
        '/Contracts/Support/CanBeEscapedWhenCastToString.php',
    ];

    const FS_FILES = [
        '/Contracts/Filesystem/FileNotFoundException.php',
        '/Filesystem/Filesystem.php',
    ];

    protected static $defaultName = 'update';

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $cwd;

    /**
     * @var string
     */
    private $release;

    /**
     * @var \Symfony\Component\Console\Helper\ProgressBar
     */
    private $progress;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    protected function configure()
    {
        $this
            ->addOption(
                'release',
                'r',
                InputOption::VALUE_REQUIRED,
                'The Laravel/Framework version to update to'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->release = 'v' . $input->getOption('release');
        $this->input = $input;
        $this->output = $output;

        $this->filesystem = new Filesystem();
        $this->cwd = realpath(__DIR__ . '/../');

        $this->flushPreviousRelease();
        $this->getTests();
        $this->getSupport();
        $this->getViews();
        $this->getFilesystem();
        return 0;
    }

    private function getSupport()
    {
        foreach (self::SUPPORT_FILES as $file) {
            $files[] = self::RAW_BASE . $this->release . '/src/Illuminate' . $file;
        }

        $this->downloadFiles($files, 'support');
    }

    private function getViews()
    {
        foreach (self::VIEW_FILES as $file) {
            $files[] = self::RAW_BASE . $this->release . '/src/Illuminate' . $file;
        }

        $this->downloadFiles($files, 'view');
    }

    private function getFilesystem()
    {
        foreach (self::FS_FILES as $file) {
            $files[] = self::RAW_BASE . $this->release . '/src/Illuminate' . $file;
        }

        $this->downloadFiles($files, 'Filesystem');
    }

    private function getTests()
    {
        $this->startSection('Gathering test files for download...');

        $curl = new Curl();
        $curl->get(self::REPO_BASE . $this->release . '/tests/View/Blade/');

        if ($curl->error) {
            $this->output->writeln("<comment>Error: ' . $curl->errorCode . ': ' . $curl->errorMessage</comment>");
            exit;
        } else {
            $crawler = new Crawler($curl->response);

            $links = [];
            $test = json_decode($crawler->filter('[data-target="react-app.embeddedData"]')->text());

            foreach ($test->payload->tree->items as $file) {
                $links[] = self::RAW_BASE . $this->release . '/tests/View/Blade/' . $file->name;
            }
            $this->endSection();

            foreach (self::TEST_FILES as $file) {
                $links[] = self::RAW_BASE . $this->release . '/tests' . $file;
            }

            $this->downloadFiles(array_values(\array_filter($links)), 'test');
        }
    }

    /**
     * Download raw files from github.
     *
     * @param array  $links
     * @param string $type
     * @throws \ErrorException
     */
    private function downloadFiles(array $links, string $type)
    {
        $this->startSection("Downloading {$type} files...", count($links));

        $total = count($links);

        $multiCurl = new MultiCurl();

        $multiCurl->success(function ($instance) {
            $this->copyRawFile($instance->url, $instance->response);
        });

        $multiCurl->error(function ($instance) {
            $this->output->writeln("<comment>Error: call to {$instance->url} was unsuccessful.\n{$instance->errorCode} : {$instance->errorMessage}</comment>");
            exit;
        });

        $multiCurl->complete(function () use (&$total) {
            if (--$total === 0) {
                $this->endSection();
            }
        });

        foreach ($links as $link) {
            $multiCurl->addGet($link);
        }

        $multiCurl->start();
    }

    private function flushPreviousRelease()
    {
        $this->filesystem->remove([
            $this->cwd . '/tests/Illuminate/',
        ]);
    }

    private function copyRawFile($url, $response)
    {
        $path = \str_replace(
            [
                self::RAW_BASE . $this->release,
                'Illuminate/',
                '/tests/',
            ],
            [
                '',
                '',
                '/tests/Illuminate/',
            ],
            $url
        );


        $this->filesystem->dumpFile(
            $this->cwd . $path,
            $this->convertNamespace($response)
        );

        $this->progress->advance();
    }

    private function convertNamespace($response)
    {
        $rewrites = [
            // Namespace conversions
            ' Arr::' => ' \\Illuminate\\Support\\Arr::',
            'Illuminate\\Tests\\' => 'Bladezero\\Tests\\',

            '\\Illuminate\\\\' => '\\Unseenco\\\\Blade\\\\',
            'Illuminate\\' => 'Bladezero\\',
            'Bladezero\\Support\\Arr' => 'Illuminate\\Support\\Arr',
            'Bladezero\\Tests\\' => 'Bladezero\\Tests\\Illuminate\\',
            '\\Bladezero\\Support\\Collection' => '\\Illuminate\\Support\\Collection',
            'use Bladezero\\Support\\Collection' => 'use Illuminate\\Support\\Collection',

            'Bladezero\\Support\\Traits\\Macroable' => '\\Illuminate\\Support\\Traits\\Macroable',
            'Bladezero\\View\\Factory' => 'Bladezero\\Factory',
            'new StringableObjectStub' => 'new \\Bladezero\\Tests\\Stubs\\StringableObjectStub',
            'use Bladezero\\Contracts\\View\\Factory;' => 'use Bladezero\\Factory;',
            'use Bladezero\Container\Container;' => '',
            '$viewFactory = Container::getInstance()->make(Factory::class);' => '',
            '$viewFactory->exists' => 'Factory::exists',
            '__construct(Factory $' => '__construct(\\BladeZero\\Factory $',
            '$this->factory->callComposer($this);' => '',
            //'$componentNamespace = \'Blade\\Components\';' => '$componentNamespace = \'App\\View\\Components\\\';',

            // Compiler amends
            '\TIlluminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render()' => '\TIlluminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))',
            "\TIlluminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render()" => "\TIlluminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))",
            '\Bladezero\Support\Facades\Blade::check' => '\$__env->getCompiler()->check',
            '\$__env->getCompiler()->check(' . "\'custom\'" => '$__env->getCompiler()->check(' . "\'custom\'",
            '$this->componentData($name))->render()' => '$this->componentData($name))',
            'csrf_field();' => "\'" . '<input type="hidden" name="_token" value="' . "\'." . '$__env->getCsrfToken()' . ".\'" . '">' . "\';",

            // auth rewrites
            'auth()->guard{$guard}->check()' => '\$__env->authHandler{$guard}',
            'auth()->guard{$guard}->guest()' => '! \$__env->authHandler{$guard}',
            'auth()->guard("api")->check()' => '$__env->authHandler("api")',
            'auth()->guard("standard")->check()' => '$__env->authHandler("standard")',
            'auth()->guard("api")->guest()' => '! $__env->authHandler("api")',
            'auth()->guard("standard")->guest()' => '! $__env->authHandler("standard")',
            'auth()->guard()->check()' => '$__env->authHandler()',

            // error rewrites
            '$__errorArgs = [\'.$expression.\'];' => '',
            "\$__bag = \$errors->getBag(\$__errorArgs[1] ?? \'default\');" => '',
            "unset(\$__errorArgs, \$__bag);" => '',
            "\$__bag->has(\$__errorArgs[0])" => '$__env->errorHandler(\'.$expression.\')',
            "\$__bag->first(\$__errorArgs[0])" => '$__env->errorHandler(\'.$expression.\')',

            "<?php \$__errorArgs = [\'email\'];\n\nif (\$__env->errorHandler('.\$expression.')) :" => "<?php \n\nif (\$__env->errorHandler(\'email\')) :",
            "if (\$__env->errorHandler(\'email\')) :\nif (isset(\$message)) { \$__messageOriginal = \$message; }\n\$message = \$__env->errorHandler('.\$expression.');" => "if (\$__env->errorHandler(\'email\')) :\nif (isset(\$message)) { \$__messageOriginal = \$message; }\n\$message = \$__env->errorHandler(\'email\');",
            "<?php \$__errorArgs = [\'email\', \'customBag\'];\n\nif (\$__env->errorHandler('.\$expression.')) :\nif (isset(\$message)) { \$__messageOriginal = \$message; }\n\$message = \$__env->errorHandler('.\$expression.');" => "<?php \n\nif (\$__env->errorHandler(\'email\', \'customBag\')) :\nif (isset(\$message)) { \$__messageOriginal = \$message; }\n\$message = \$__env->errorHandler(\'email\', \'customBag\');",


            // can rewrites
            'app(\\Unseenco\\\\Blade\\\\Contracts\\\\Auth\\\\Access\\\\Gate::class)->check{$expression}' => '\$__env->canHandler{$expression}',
            'app(\\Unseenco\\\\Blade\\\\Contracts\\\\Auth\\\\Access\\\\Gate::class)->denies{$expression}' => '! \$__env->canHandler{$expression}',
            'app(\\Unseenco\\\\Blade\\\\Contracts\\\\Auth\\\\Access\\\\Gate::class)->any{$expression}' => '\$__env->canAnyHandler{$expression}',
            'app(\\\\Unseenco\\\Blade\\\\Contracts\\\\Auth\\\\Access\\\\Gate::class)->check(' => '$__env->canHandler(',
            'app(\\\\Unseenco\\\Blade\\\\Contracts\\\\Auth\\\\Access\\\\Gate::class)->any(' => '$__env->canAnyHandler(',
            'app(\\\\Unseenco\\\Blade\\\\Contracts\\\\Auth\\\\Access\\\\Gate::class)->denies(' => '! $__env->canHandler(',


            // tests
            '$this->mockViewFactory();' => '',
            'Container::setInstance(null);' => '',
            '$container = new Container;' => '',
            '$container->instance(Application::class, $app = Mockery::mock(Application::class));' => '',
            '$app->shouldReceive(\'getNamespace\')->andReturn(\'App\\\\\');' => '',
            'Container::setInstance($container);' => '',
            '$container->instance(Factory::class, $factory = Mockery::mock(Factory::class));' => '',
            '$factory->shouldReceive(\'exists\')->andReturn(true);' => '',
            '$factory->shouldReceive(\'exists\')->andReturn(false, true);' => '$result = $this->compiler()->compileTags(\'<x-anonymous-component-index :name="\\\'Taylor\\\'" :age="31" wire:model="foo" />\');',
            'return new ComponentTagCompiler(' => '$factory = new Factory(__DIR__ . \'../../../../Bladezero/fixtures/files\', __DIR__ . \'../../../../Bladezero/fixtures/cache\');' . "\n". 'return new ComponentTagCompiler(',
            '$result = $this->compiler()->compileTags(\'<x-anonymous-component-index :name="\\\'Taylor\\\'" :age="31" wire:model="foo" />\');
        

        $result = $this->compiler()->compileTags(\'<x-anonymous-component :name="\\\'Taylor\\\'" :age="31" wire:model="foo" />\');' => '$result = $this->compiler()->compileTags(\'<x-anonymous-component-index :name="\\\'Taylor\\\'" :age="31" wire:model="foo" />\');',
            '\'anonymous-component\', [\'view\' => \'components.anonymous-component.index' => '\'anonymous-component-index\', [\'view\' => \'components.anonymous-component-index.index',
            'public function testPackagesClasslessComponents()
    {' => 'public function testPackagesClasslessComponents()
    {' . "\n" . '        $this->markTestSkipped(\'We do not suport packages\');',
            '$model = new class extends Model {};' => '',
            '$this->assertSame($model, BladeCompiler::sanitizeComponentAttribute($model));' => '',
            '$factory->shouldReceive(\'exists\')->andReturn(false);' => '',
            'public function testItThrowsAnExceptionForNonExistingClass()
    {' => 'public function testItThrowsAnExceptionForNonExistingClass()
    {'. "\n" . '        $this->markTestSkipped();',
            '$component = $__env->getContainer()->make(Test::class, ["foo" => "bar"]); ?>' => '$componentData = ["foo" => "bar"]; $component = new Test::class(["foo" => "bar"]); ?>',
            'App\View\Components\Alert' => '\Bladezero\Tests\Bladezero\Components\Alert',
            'App\View\Components\Base\Alert' => '\Bladezero\Tests\Bladezero\Components\Base\Alert',
            'app()->singleton(\'blade.compiler\', function () {
            return $this->compiler;
        });' => '$fakeInstance = new \Bladezero\Factory(realpath(\'./files\'), realpath(\'./cache\'));',
            'public function testHandlerLogicWorksCorrectly($blade)
    {' => 'public function testHandlerLogicWorksCorrectly($blade)
    {' . "\n" . '        $this->markTestSkipped();',

            //components
            '$namespace = Container::getInstance()
                    ->make(Application::class)
                    ->getNamespace();' . "\n" => '',
            '$factory = Container::getInstance()->make(\'view\');'."\n" => '',
            "\$namespace.'View\\\\Components\\\\'" => '\\Bladezero\\Factory::getComponentNamespace()',
            'Container::getInstance()->make(Factory::class)
                    ->exists' => '\\Bladezero\\Factory::exists',
            '$factory->exists' => '\\Bladezero\\Factory::exists',
            '$this->createBladeViewFromString($factory' => '$this->createBladeViewFromString(null',
            'return $this->make($view, $this->componentData())->render();' => 'return $this->make($view, $this->componentData());',
            "<?php \$component = \$__env->getContainer()->make('.Str::finish(\$component, '::class').', '.(\$data ?: '[]').'); ?>" => "<?php \$componentData = '.\$data.'; \$component = new '.\$component.'('. \$params .'); ?>",
            "public static function compileClassComponentOpening(string \$component, string \$alias, string \$data, string \$hash)
    {" => "public static function compileClassComponentOpening(string \$component, string \$alias, string \$data, string \$hash)
    {
        if (\$component === 'Bladezero\View\AnonymousComponent') {
            \$params = '\$componentData[\'view\'], (\$componentData[\'data\'] ?: [])';
        } elseif (\$component === 'Bladezero\View\DynamicComponent') {
            \$params = '\$componentData[\'component\']';
        }  elseif (class_exists(\$component) && is_subclass_of(\$component, \Bladezero\View\Component::class)) {
            \$params = '...' . (\$data ?: '[]');
        } else {
            \$params = (\$data ?: '[]');
        }",

            // dynamic component fixes
            '$view = value($view, $data);' => '$view = $view instanceof \Closure ? $view($data) : value($view, $data);',
            '$factory->addNamespace(
            \'__components\',
            $directory = Container::getInstance()[\'config\']->get(\'view.compiled\')
        );' => '\\Bladezero\\Factory::getFinderStatic()->addNamespace(
	        \'__components\',
	        $directory = \\Bladezero\\Factory::getCompiledPath()
        );',
            'app(\'blade.compiler\')' => '\\Bladezero\\Factory::getCompiler()',

            // inject rewrites
            'app(\'{$service}\')' => '\$__env->injectHandler(\'{$service}\')',
            'app({$service})' => '\$__env->injectHandler({$service})',
            'app(\'SomeNamespace\\SomeClass\')' => '\$__env->injectHandler(\'SomeNamespace\\SomeClass\')',
            'app("SomeNamespace\\SomeClass")' => '$__env->injectHandler("SomeNamespace\\SomeClass")',
            'app(SomeNamespace\\SomeClass::class)' => '\$__env->injectHandler(SomeNamespace\\SomeClass::class)',
            'Container::getInstance()->make(\'blade.compiler\')' => '\\Bladezero\\Factory::getBladeCompilerStatic()',

            'use Symfony\Component\Debug\Exception\FatalThrowableError;' => '',
            'FatalThrowableError' => 'Exception',
            'PHP_EOL' => '"\n"',
        ];

        return \str_replace(array_keys($rewrites), \array_values($rewrites), $response);
    }

    private function startSection(string $message, int $steps = 1): void
    {
        $this->output->writeln("\n\n<info>{$message}</info>");
        $this->progress = new ProgressBar($this->output, $steps);
        $this->progress->setMaxSteps($steps);
        $this->progress->start();
    }

    private function endSection()
    {
        $this->progress->finish();
    }
}
