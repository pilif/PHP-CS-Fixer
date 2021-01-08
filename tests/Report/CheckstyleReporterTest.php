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

namespace PhpCsFixer\Tests\Report;

use PhpCsFixer\PhpunitConstraintXmlMatchesXsd\Constraint\XmlMatchesXsd;
use PhpCsFixer\Report\CheckstyleReporter;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Report\CheckstyleReporter
 */
final class CheckstyleReporterTest extends AbstractReporterTestCase
{
    /**
     * "checkstyle" XML schema.
     *
     * @var null|string
     */
    private static $xsd;

    public static function doSetUpBeforeClass()
    {
        self::$xsd = file_get_contents(__DIR__.'/../../doc/report-schema/checkstyle.xsd');
    }

    public static function doTearDownAfterClass()
    {
        self::$xsd = null;
    }

    protected function createNoErrorReport()
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<checkstyle />
XML;
    }

    protected function createSimpleReport()
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
  <file name="someFile.php">
    <error severity="warning" source="PHP-CS-Fixer.some_fixer_name_here" message="Found violation(s) of type: some_fixer_name_here" />
  </file>
</checkstyle>
XML;
    }

    protected function createWithDiffReport()
    {
        // NOTE: checkstyle format does NOT include diffs
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
  <file name="someFile.php">
    <error severity="warning" source="PHP-CS-Fixer.some_fixer_name_here" message="Found violation(s) of type: some_fixer_name_here" />
  </file>
</checkstyle>
XML;
    }

    protected function createWithAppliedFixersReport()
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
  <file name="someFile.php">
    <error severity="warning" source="PHP-CS-Fixer.some_fixer_name_here_1" message="Found violation(s) of type: some_fixer_name_here_1" />
    <error severity="warning" source="PHP-CS-Fixer.some_fixer_name_here_2" message="Found violation(s) of type: some_fixer_name_here_2" />
  </file>
</checkstyle>
XML;
    }

    protected function createWithTimeAndMemoryReport()
    {
        // NOTE: checkstyle format does NOT include time or memory
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
  <file name="someFile.php">
    <error severity="warning" source="PHP-CS-Fixer.some_fixer_name_here" message="Found violation(s) of type: some_fixer_name_here" />
  </file>
</checkstyle>
XML;
    }

    protected function createComplexReport()
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
  <file name="someFile.php">
    <error severity="warning" source="PHP-CS-Fixer.some_fixer_name_here_1" message="Found violation(s) of type: some_fixer_name_here_1" />
    <error severity="warning" source="PHP-CS-Fixer.some_fixer_name_here_2" message="Found violation(s) of type: some_fixer_name_here_2" />
  </file>
  <file name="anotherFile.php">
    <error severity="warning" source="PHP-CS-Fixer.another_fixer_name_here" message="Found violation(s) of type: another_fixer_name_here" />
  </file>
</checkstyle>
XML;
    }

    protected function createReporter()
    {
        return new CheckstyleReporter();
    }

    protected function getFormat()
    {
        return 'checkstyle';
    }

    protected function assertFormat($expected, $input)
    {
        $formatter = new OutputFormatter();
        $input = $formatter->format($input);

        static::assertThat($input, new XmlMatchesXsd(self::$xsd));
        static::assertXmlStringEqualsXmlString($expected, $input);
    }
}
