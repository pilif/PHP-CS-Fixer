<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests;

use PhpCsFixer\Config;
use PhpCsFixer\Console\Application;
use PhpCsFixer\Console\Command\FixCommand;
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\Finder;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\ToolInfo;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Finder\Finder as SymfonyFinder;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Config
 */
final class ConfigTest extends TestCase
{
    public function testConfigRulesUsingSeparateMethod()
    {
        $config = new Config();
        $configResolver = new ConfigurationResolver(
            $config,
            [
                'rules' => 'cast_spaces,braces',
            ],
            getcwd(),
            new ToolInfo()
        );

        static::assertSame(
            [
                'cast_spaces' => true,
                'braces' => true,
            ],
            $configResolver->getRules()
        );
    }

    public function testConfigRulesUsingJsonMethod()
    {
        $config = new Config();
        $configResolver = new ConfigurationResolver(
            $config,
            [
                'rules' => '{"array_syntax": {"syntax": "short"}, "cast_spaces": true}',
            ],
            getcwd(),
            new ToolInfo()
        );

        static::assertSame(
            [
                'array_syntax' => [
                    'syntax' => 'short',
                ],
                'cast_spaces' => true,
            ],
            $configResolver->getRules()
        );
    }

    public function testConfigRulesUsingInvalidJson()
    {
        $this->expectException(\PhpCsFixer\ConfigurationException\InvalidConfigurationException::class);

        $config = new Config();
        $configResolver = new ConfigurationResolver(
            $config,
            [
                'rules' => '{blah',
            ],
            getcwd(),
            new ToolInfo()
        );
        $configResolver->getRules();
    }

    public function testCustomConfig()
    {
        $customConfigFile = __DIR__.'/Fixtures/.php_cs_custom.php';

        $application = new Application();
        $application->add(new FixCommand(new ToolInfo()));

        $commandTester = new CommandTester($application->find('fix'));

        $commandTester->execute(
            [
                'path' => [$customConfigFile],
                '--dry-run' => true,
                '--config' => $customConfigFile,
            ],
            [
                'decorated' => false,
                'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE,
            ]
        );
        static::assertStringMatchesFormat(
            sprintf('%%ALoaded config custom_config_test from "%s".%%A', $customConfigFile),
            $commandTester->getDisplay(true)
        );
    }

    public function testThatFinderWorksWithDirSetOnConfig()
    {
        $config = new Config();

        $items = iterator_to_array(
            $config->getFinder()->in(__DIR__.'/Fixtures/FinderDirectory'),
            false
        );

        static::assertCount(1, $items);
        static::assertSame('somefile.php', $items[0]->getFilename());
    }

    public function testThatCustomFinderWorks()
    {
        $finder = new Finder();
        $finder->in(__DIR__.'/Fixtures/FinderDirectory');

        $config = (new Config())->setFinder($finder);

        $items = iterator_to_array(
            $config->getFinder(),
            false
        );

        static::assertCount(1, $items);
        static::assertSame('somefile.php', $items[0]->getFilename());
    }

    public function testThatCustomSymfonyFinderWorks()
    {
        $finder = new SymfonyFinder();
        $finder->in(__DIR__.'/Fixtures/FinderDirectory');

        $config = (new Config())->setFinder($finder);

        $items = iterator_to_array(
            $config->getFinder(),
            false
        );

        static::assertCount(1, $items);
        static::assertSame('somefile.php', $items[0]->getFilename());
    }

    public function testThatCacheFileHasDefaultValue()
    {
        $config = new Config();

        static::assertSame('.php_cs.cache', $config->getCacheFile());
    }

    public function testThatCacheFileCanBeMutated()
    {
        $cacheFile = 'some-directory/some.file';

        $config = new Config();
        $config->setCacheFile($cacheFile);

        static::assertSame($cacheFile, $config->getCacheFile());
    }

    public function testThatMutatorHasFluentInterface()
    {
        $config = new Config();

        static::assertSame($config, $config->setCacheFile('some-directory/some.file'));
    }

    public function testRegisterCustomFixersWithInvalidArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^Argument must be an array or a Traversable, got "\w+"\.$/');

        $config = new Config();
        $config->registerCustomFixers('foo');
    }

    /**
     * @param FixerInterface[] $expected
     * @param iterable         $suite
     *
     * @dataProvider provideRegisterCustomFixersCases
     */
    public function testRegisterCustomFixers(array $expected, $suite)
    {
        $config = new Config();
        $config->registerCustomFixers($suite);

        static::assertSame($expected, $config->getCustomFixers());
    }

    public function testConfigDefault()
    {
        $config = new Config();

        static::assertSame('.php_cs.cache', $config->getCacheFile());
        static::assertSame([], $config->getCustomFixers());
        static::assertSame('txt', $config->getFormat());
        static::assertFalse($config->getHideProgress());
        static::assertSame('    ', $config->getIndent());
        static::assertSame("\n", $config->getLineEnding());
        static::assertSame('default', $config->getName());
        static::assertNull($config->getPhpExecutable());
        static::assertFalse($config->getRiskyAllowed());
        static::assertSame(['@PSR2' => true], $config->getRules());
        static::assertTrue($config->getUsingCache());

        $finder = $config->getFinder();
        static::assertInstanceOf(Finder::class, $finder);

        $config->setFormat('xml');
        static::assertSame('xml', $config->getFormat());

        $config->setHideProgress(true);
        static::assertTrue($config->getHideProgress());

        $config->setIndent("\t");
        static::assertSame("\t", $config->getIndent());

        $finder = new Finder();
        $config->setFinder($finder);
        static::assertSame($finder, $config->getFinder());

        $config->setLineEnding("\r\n");
        static::assertSame("\r\n", $config->getLineEnding());

        $config->setPhpExecutable(null);
        static::assertNull($config->getPhpExecutable());

        $config->setUsingCache(false);
        static::assertFalse($config->getUsingCache());
    }

    public function testSetInvalidFinder()
    {
        $config = new Config();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^Argument must be an array or a Traversable, got "integer"\.$/');

        $config->setFinder(123);
    }

    /**
     * @return array
     */
    public function provideRegisterCustomFixersCases()
    {
        $fixers = [
            new NoWhitespaceBeforeCommaInArrayFixer(),
            new IncludeFixer(),
        ];

        return [
            [$fixers, $fixers],
            [$fixers, new \ArrayIterator($fixers)],
        ];
    }

    public function testConfigConstructorWithName()
    {
        $anonymousConfig = new Config();
        $namedConfig = new Config('foo');

        static::assertSame($anonymousConfig->getName(), 'default');
        static::assertSame($namedConfig->getName(), 'foo');
    }

    /**
     * @group legacy
     * @expectedDeprecation PhpCsFixer\Config::create is deprecated since 2.17 and will be removed in 3.0.
     */
    public function testDeprecatedConstructor()
    {
        Config::create();
    }
}
