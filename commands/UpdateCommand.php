<?php

namespace Rapier\Commands;

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
        '/View/ViewFileViewFinderTest.php'
    ];

    const SUPPORT_FILES = [
        '/Support/Traits/Macroable.php',
        '/Support/Str.php',
        '/Support/Pluralizer.php',
        '/Support/HigherOrderTapProxy.php',
    ];

    const VIEW_FILES = [
        '/View/Compilers/BladeCompiler.php',
        '/View/Compilers/Compiler.php',
        '/View/Compilers/CompilerInterface.php',
        '/View/Compilers/Concerns/CompilesAuthorizations.php',
        '/View/Compilers/Concerns/CompilesComments.php',
        '/View/Compilers/Concerns/CompilesAuthorizations.php',
        '/View/Compilers/Concerns/CompilesConditionals.php',
        '/View/Compilers/Concerns/CompilesEchos.php',
        '/View/Compilers/Concerns/CompilesErrors.php',
        '/View/Compilers/Concerns/CompilesHelpers.php',
        '/View/Compilers/Concerns/CompilesIncludes.php',
        '/View/Compilers/Concerns/CompilesInjections.php',
        '/View/Compilers/Concerns/CompilesJson.php',
        '/View/Compilers/Concerns/CompilesLayouts.php',
        '/View/Compilers/Concerns/CompilesLoops.php',
        '/View/Compilers/Concerns/CompilesRawPhp.php',
        '/View/Compilers/Concerns/CompilesStacks.php',
        '/View/Compilers/Concerns/CompilesTranslations.php',
        '/View/Concerns/ManagesComponents.php',
        '/View/Concerns/ManagesEvents.php',
        '/View/Concerns/ManagesLayouts.php',
        '/View/Concerns/ManagesLoops.php',
        '/View/Concerns/ManagesStacks.php',
        '/View/Concerns/ManagesTranslations.php',
        '/View/Engines/CompilerEngine.php',
        '/View/Engines/Engine.php',
        '/View/Engines/EngineResolver.php',
        '/View/Engines/FileEngine.php',
        '/View/Engines/PhpEngine.php',
        '/Contracts/Support/Arrayable.php',
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
                null,
                InputOption::VALUE_REQUIRED,
                'The Laravel/framework version to update to'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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
            $files[] = self::RAW_BASE . $this->release . '/src/Illuminate'. $file;
        }

        $this->downloadFiles($files, 'support');
    }

    private function getViews()
    {
        foreach (self::VIEW_FILES as $file) {
            $files[] = self::RAW_BASE . $this->release . '/src/Illuminate'. $file;
        }

        $this->downloadFiles($files, 'view');
    }

    private function getFilesystem()
    {
        foreach (self::FS_FILES as $file) {
            $files[] = self::RAW_BASE . $this->release . '/src/Illuminate'. $file;
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

            $links = $crawler
                ->filter('.files a.js-navigation-open')
                ->each(function (Crawler $node) {
                    if (\strpos($node->attr('title'), '.php') === false) {
                        return false;
                    }

                    return self::RAW_BASE . $this->release . '/tests/View/Blade/' . $node->attr('title');
                });

            $this->endSection();

            foreach (self::TEST_FILES as $file) {
                $links[] = self::RAW_BASE . $this->release . '/tests'. $file;
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

        $multiCurl->success(function($instance) {
            $this->copyRawFile($instance->url, $instance->response);
        });

        $multiCurl->error(function($instance) {
            $this->output->writeln("<comment>Error: call to {$instance->url} was unsuccessful.\n{$instance->errorCode} : {$instance->errorMessage}</comment>");
            exit;
        });

        $multiCurl->complete(function() use (&$total) {
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
                '/tests/'
            ],
            [
                '',
                '',
                '/tests/Illuminate/'
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
            '\\Illuminate\\Support\\Arr::' => '\\Tightenco\\Collect\\Support\\Arr::',
            ' Arr::' => ' \\Tightenco\\Collect\\Support\\Arr::',
            'Illuminate\\Tests\\' => 'Rapier\\Tests\\',
            'Illuminate\\Support\\Arr' => 'Tightenco\\Collect\\Support\\Arr',
            'Illuminate\\' => 'Rapier\\',
            'Rapier\\Tests\\' => 'Rapier\\Tests\\Illuminate\\',

            // Compiler amends
            '\Tightenco\Collect\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render()' => '\Tightenco\Collect\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))',
            "\Tightenco\Collect\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render()" => "\Tightenco\Collect\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))",

            // auth rewrites
            'auth()->guard{$guard}->check()' => '\$__env->authHandler{$guard}',
            'auth()->guard{$guard}->guest()' => '! \$__env->authHandler{$guard}',
            'auth()->guard("api")->check()' => '$__env->authHandler("api")',
            'auth()->guard("standard")->check()' => '$__env->authHandler("standard")',
            'auth()->guard("api")->guest()' => '! $__env->authHandler("api")',
            'auth()->guard("standard")->guest()' => '! $__env->authHandler("standard")',
            'auth()->guard()->check()' => '$__env->authHandler()',

            // inject rewrites
            'app(\'{$service}\')' => '\$__env->injectHandler(\'{$service}\')',

            'use Symfony\Component\Debug\Exception\FatalThrowableError;' => '',
            'FatalThrowableError' => 'Exception',
            'Rapier\\View\\Factory' => 'Rapier\\Blade'
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