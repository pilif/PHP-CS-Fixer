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

namespace PhpCsFixer\Tests\Fixer\ClassNotation;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @author Gregor Harlan <gharlan@web.de>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer
 */
final class OrderedClassElementsFixerTest extends AbstractFixerTestCase
{
    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideFixCases
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideFixCases()
    {
        return [
            [
                <<<'EOT'
<?php

class Foo {}

class Bar
{
}
EOT
            ],
            [
                <<<'EOT'
<?php

class Foo { const C1 = 1; protected $abc = 'abc'; public function baz($y, $z) {} private function bar1($x) { return 1; } }
EOT
                , <<<'EOT'
<?php

class Foo { private function bar1($x) { return 1; } protected $abc = 'abc'; const C1 = 1; public function baz($y, $z) {} }
EOT
            ],
            [
                <<<'EOT'
<?php

interface FooInterface
{

    const CONST1 = 'const1';

    const CONST2 = 'const2';
    public function xyz($x, $y, $z); // comment

    /**
     * @param array $a
     *
     * @return string
     */
    function abc(array &$a = null);

    public function def();
}
EOT
                , <<<'EOT'
<?php

interface FooInterface
{
    public function xyz($x, $y, $z); // comment

    const CONST1 = 'const1';

    /**
     * @param array $a
     *
     * @return string
     */
    function abc(array &$a = null);

    const CONST2 = 'const2';

    public function def();
}
EOT
            ],
            [
                <<<'EOT'
<?php

abstract class Foo extends FooParent implements FooInterface1, FooInterface2
{

    use Bar;

    use Baz {
        abc as private;
    }

    const C1 = 1;
    /* comment for C2 */

    const C2 = 2;

    public $fooPublic;

    // comment 3

    protected $fooProtected = array(1, 2);
    // comment 1

    private $fooPrivate;

    protected function __construct()
    {
    }

    public function __destruct() {}

    public function __clone() {}

    public static function setUpBeforeClass() {}

    public static function teardownafterclass() {
    } /* multiline
    comment */

    protected function setUp() {}

    protected function doSetUp() {}

    protected function tearDown() {}

    protected function doTearDown() {}

    abstract public function foo1($a, $b = 1);

    // foo2
    function foo2()
    {
        return $this->foo1(1);
    }

    public static function foo3(self $foo)
    {
        return $foo->foo2();
    } /* comment 1 */ /* comment 2 */

    // comment

    /**
     * Docblock
     */
    protected function foo4(\ArrayObject $object, array $array, $a = null)
    {
        bar();

        if (!$a) {
            $a = function ($x) {
                var_dump($x);
            };
        }
    }

    private function foo5()
    {
    } // end foo5
}
EOT
                , <<<'EOT'
<?php

abstract class Foo extends FooParent implements FooInterface1, FooInterface2
{
    // comment 1

    private $fooPrivate;

    abstract public function foo1($a, $b = 1);

    protected function tearDown() {}

    protected function doSetUp() {}

    protected function doTearDown() {}

    public function __clone() {}

    const C1 = 1;

    // foo2
    function foo2()
    {
        return $this->foo1(1);
    }

    public static function setUpBeforeClass() {}

    public function __destruct() {}

    use Bar;

    public static function foo3(self $foo)
    {
        return $foo->foo2();
    } /* comment 1 */ /* comment 2 */

    // comment 3

    protected $fooProtected = array(1, 2);

    public $fooPublic;
    /* comment for C2 */

    const C2 = 2;

    public static function teardownafterclass() {
    } /* multiline
    comment */

    use Baz {
        abc as private;
    }

    private function foo5()
    {
    } // end foo5

    protected function setUp() {}

    protected function __construct()
    {
    }

    // comment

    /**
     * Docblock
     */
    protected function foo4(\ArrayObject $object, array $array, $a = null)
    {
        bar();

        if (!$a) {
            $a = function ($x) {
                var_dump($x);
            };
        }
    }
}
EOT
            ],
            [
                <<<'EOT'
<?php

class Foo
{
    const C = 'C';
    public function abc() {}
    protected function xyz() {}
}

class Bar
{
    const C = 1;
    public function foo($a) { return 1; }
    public function baz() {}
}
EOT
                , <<<'EOT'
<?php

class Foo
{
    protected function xyz() {}
    const C = 'C';
    public function abc() {}
}

class Bar
{
    public function foo($a) { return 1; }
    const C = 1;
    public function baz() {}
}
EOT
            ],
            [
                <<<'EOT'
<?php

trait FooTrait
{

    use BarTrait;

    use BazTrait;
    protected function abc() {
    }
}
EOT
                , <<<'EOT'
<?php

trait FooTrait
{
    protected function abc() {
    }

    use BarTrait;

    use BazTrait;
}
EOT
            ],
            [
                <<<'EOT'
<?php

/**
 * @see #4086
 */
class ComplexStringVariableAndUseTrait
{
    use FooTrait;
    public function sayHello($name)
    {
        echo "Hello, {$name}!";
    }
}

EOT
                ,
                <<<'EOT'
<?php

/**
 * @see #4086
 */
class ComplexStringVariableAndUseTrait
{
    public function sayHello($name)
    {
        echo "Hello, {$name}!";
    }
    use FooTrait;
}

EOT
            ],
        ];
    }

    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideFix71Cases
     * @requires PHP 7.1
     */
    public function testFix71(array $configuration, $expected, $input = null)
    {
        $this->fixer->configure($configuration);
        $this->doTest($expected, $input);
    }

    public function provideFix71Cases()
    {
        return [
            [
                [],
                <<<'EOT'
<?php

class Foo
{
    const C2 = 2;
    public const C1 = 1;
    public const C3 = 3;
    protected const C4 = 4;
    private const C5 = 5;
}
EOT
                , <<<'EOT'
<?php

class Foo
{
    private const C5 = 5;
    const C2 = 2;
    public const C1 = 1;
    protected const C4 = 4;
    public const C3 = 3;
}
EOT
            ],
            [
                ['sort_algorithm' => 'alpha'],
                <<<'EOT'
<?php

class Foo
{
    public const C1 = 1;
    const C2 = 2;
    public const C3 = 3;
    protected const C4 = 4;
    private const C5 = 5;
}
EOT
                , <<<'EOT'
<?php

class Foo
{
    private const C5 = 5;
    const C2 = 2;
    public const C1 = 1;
    protected const C4 = 4;
    public const C3 = 3;
}
EOT
            ],
        ];
    }

    /**
     * @param string $input
     * @param string $expected
     *
     * @group legacy
     * @dataProvider provideConfigurationCases
     * @expectedDeprecation Passing "order" at the root of the configuration for rule "ordered_class_elements" is deprecated and will not be supported in 3.0, use "order" => array(...) option instead.
     */
    public function testLegacyFixWithConfiguration(array $configuration, $expected, $input)
    {
        $this->fixer->configure($configuration);
        $this->doTest($expected, $input);
    }

    /**
     * @param string $input
     * @param string $expected
     *
     * @dataProvider provideConfigurationCases
     */
    public function testFixWithConfiguration(array $configuration, $expected, $input)
    {
        $this->fixer->configure(['order' => $configuration]);
        $this->doTest($expected, $input);
    }

    public function provideConfigurationCases()
    {
        return [
            [
                ['use_trait', 'constant', 'property', 'construct', 'method', 'destruct'],
                <<<'EOT'
<?php

class Foo
{
    use BarTrait;
    use BazTrait;
    const C1 = 1;
    const C2 = 2;
    protected static $protStatProp;
    public static $pubStatProp1;
    public $pubProp1;
    protected $protProp;
    var $pubProp2;
    private static $privStatProp;
    private $privProp;
    public static $pubStatProp2;
    public $pubProp3;
    protected function __construct() {}
    private static function privStatFunc() {}
    public function pubFunc1() {}
    public function __toString() {}
    protected function protFunc() {}
    function pubFunc2() {}
    public static function pubStatFunc1() {}
    public function pubFunc3() {}
    static function pubStatFunc2() {}
    private function privFunc() {}
    public static function pubStatFunc3() {}
    protected static function protStatFunc() {}
    public function __destruct() {}
}
EOT
                ,
                <<<'EOT'
<?php

class Foo
{
    private static function privStatFunc() {}
    protected static $protStatProp;
    public static $pubStatProp1;
    public function pubFunc1() {}
    use BarTrait;
    public $pubProp1;
    public function __toString() {}
    protected function protFunc() {}
    protected $protProp;
    function pubFunc2() {}
    public function __destruct() {}
    var $pubProp2;
    private static $privStatProp;
    use BazTrait;
    public static function pubStatFunc1() {}
    public function pubFunc3() {}
    private $privProp;
    const C1 = 1;
    static function pubStatFunc2() {}
    private function privFunc() {}
    public static $pubStatProp2;
    protected function __construct() {}
    const C2 = 2;
    public static function pubStatFunc3() {}
    public $pubProp3;
    protected static function protStatFunc() {}
}
EOT
                ,
            ],
            [
                ['public', 'protected', 'private'],
                <<<'EOT'
<?php

class Foo
{
    public static $pubStatProp1;
    public function pubFunc1() {}
    public $pubProp1;
    public function __toString() {}
    function pubFunc2() {}
    public function __destruct() {}
    var $pubProp2;
    public static function pubStatFunc1() {}
    public function pubFunc3() {}
    const C1 = 1;
    static function pubStatFunc2() {}
    public static $pubStatProp2;
    const C2 = 2;
    public static function pubStatFunc3() {}
    public $pubProp3;
    protected static $protStatProp;
    protected function protFunc() {}
    protected $protProp;
    protected function __construct() {}
    protected static function protStatFunc() {}
    private static function privStatFunc() {}
    private static $privStatProp;
    private $privProp;
    private function privFunc() {}
    use BarTrait;
    use BazTrait;
}
EOT
                ,
                <<<'EOT'
<?php

class Foo
{
    private static function privStatFunc() {}
    protected static $protStatProp;
    public static $pubStatProp1;
    public function pubFunc1() {}
    use BarTrait;
    public $pubProp1;
    public function __toString() {}
    protected function protFunc() {}
    protected $protProp;
    function pubFunc2() {}
    public function __destruct() {}
    var $pubProp2;
    private static $privStatProp;
    use BazTrait;
    public static function pubStatFunc1() {}
    public function pubFunc3() {}
    private $privProp;
    const C1 = 1;
    static function pubStatFunc2() {}
    private function privFunc() {}
    public static $pubStatProp2;
    protected function __construct() {}
    const C2 = 2;
    public static function pubStatFunc3() {}
    public $pubProp3;
    protected static function protStatFunc() {}
}
EOT
                ,
            ],
            [
                [
                    'use_trait',
                    'constant',
                    'property_public_static',
                    'property_protected_static',
                    'property_private_static',
                    'property_public',
                    'property_protected',
                    'property_private',
                    'construct',
                    'destruct',
                    'magic',
                    'method_public_static',
                    'method_protected_static',
                    'method_private_static',
                    'method_public',
                    'method_protected',
                    'method_private',
                ],
                <<<'EOT'
<?php

class Foo
{
    use BarTrait;
    use BazTrait;
    const C1 = 1;
    const C2 = 2;
    public static $pubStatProp1;
    public static $pubStatProp2;
    protected static $protStatProp;
    private static $privStatProp;
    public $pubProp1;
    var $pubProp2;
    public $pubProp3;
    protected $protProp;
    private $privProp;
    protected function __construct() {}
    public function __destruct() {}
    public function __toString() {}
    public static function pubStatFunc1() {}
    static function pubStatFunc2() {}
    public static function pubStatFunc3() {}
    protected static function protStatFunc() {}
    private static function privStatFunc() {}
    public function pubFunc1() {}
    function pubFunc2() {}
    public function pubFunc3() {}
    protected function protFunc() {}
    private function privFunc() {}
}
EOT
                ,
                <<<'EOT'
<?php

class Foo
{
    private static function privStatFunc() {}
    protected static $protStatProp;
    public static $pubStatProp1;
    public function pubFunc1() {}
    use BarTrait;
    public $pubProp1;
    public function __toString() {}
    protected function protFunc() {}
    protected $protProp;
    function pubFunc2() {}
    public function __destruct() {}
    var $pubProp2;
    private static $privStatProp;
    use BazTrait;
    public static function pubStatFunc1() {}
    public function pubFunc3() {}
    private $privProp;
    const C1 = 1;
    static function pubStatFunc2() {}
    private function privFunc() {}
    public static $pubStatProp2;
    protected function __construct() {}
    const C2 = 2;
    public static function pubStatFunc3() {}
    public $pubProp3;
    protected static function protStatFunc() {}
}
EOT
                ,
            ],
            [
                ['use_trait', 'constant', 'property', 'construct', 'method', 'destruct'],
                <<<'EOT'
<?php

abstract class Foo
{
    use BarTrait;
    use BazTrait;
    const C1 = 1;
    const C2 = 2;
    protected static $protStatProp;
    public static $pubStatProp1;
    public $pubProp1;
    protected $protProp;
    var $pubProp2;
    private static $privStatProp;
    private $privProp;
    public static $pubStatProp2;
    public $pubProp3;
    protected function __construct() {}
    abstract public function absPubFunc();
    private static function privStatFunc() {}
    public function pubFunc1() {}
    abstract protected function absProtFunc();
    public function __toString() {}
    protected function protFunc() {}
    function pubFunc2() {}
    abstract protected static function absProtStatFunc();
    public static function pubStatFunc1() {}
    public function pubFunc3() {}
    static function pubStatFunc2() {}
    private function privFunc() {}
    abstract public static function absPubStatFunc();
    public static function pubStatFunc3() {}
    protected static function protStatFunc() {}
    public function __destruct() {}
}
EOT
                ,
                <<<'EOT'
<?php

abstract class Foo
{
    abstract public function absPubFunc();
    private static function privStatFunc() {}
    protected static $protStatProp;
    public static $pubStatProp1;
    public function pubFunc1() {}
    abstract protected function absProtFunc();
    use BarTrait;
    public $pubProp1;
    public function __toString() {}
    protected function protFunc() {}
    protected $protProp;
    function pubFunc2() {}
    abstract protected static function absProtStatFunc();
    public function __destruct() {}
    var $pubProp2;
    private static $privStatProp;
    use BazTrait;
    public static function pubStatFunc1() {}
    public function pubFunc3() {}
    private $privProp;
    const C1 = 1;
    static function pubStatFunc2() {}
    private function privFunc() {}
    public static $pubStatProp2;
    abstract public static function absPubStatFunc();
    protected function __construct() {}
    const C2 = 2;
    public static function pubStatFunc3() {}
    public $pubProp3;
    protected static function protStatFunc() {}
}
EOT
                ,
            ],
            [
                ['public', 'protected', 'private'],
                <<<'EOT'
<?php

abstract class Foo
{
    abstract public function absPubFunc();
    public static $pubStatProp1;
    public function pubFunc1() {}
    public $pubProp1;
    public function __toString() {}
    function pubFunc2() {}
    public function __destruct() {}
    var $pubProp2;
    public static function pubStatFunc1() {}
    public function pubFunc3() {}
    const C1 = 1;
    static function pubStatFunc2() {}
    public static $pubStatProp2;
    abstract public static function absPubStatFunc();
    const C2 = 2;
    public static function pubStatFunc3() {}
    public $pubProp3;
    protected static $protStatProp;
    abstract protected function absProtFunc();
    protected function protFunc() {}
    protected $protProp;
    abstract protected static function absProtStatFunc();
    protected function __construct() {}
    protected static function protStatFunc() {}
    private static function privStatFunc() {}
    private static $privStatProp;
    private $privProp;
    private function privFunc() {}
    use BarTrait;
    use BazTrait;
}
EOT
                ,
                <<<'EOT'
<?php

abstract class Foo
{
    abstract public function absPubFunc();
    private static function privStatFunc() {}
    protected static $protStatProp;
    public static $pubStatProp1;
    public function pubFunc1() {}
    abstract protected function absProtFunc();
    use BarTrait;
    public $pubProp1;
    public function __toString() {}
    protected function protFunc() {}
    protected $protProp;
    function pubFunc2() {}
    abstract protected static function absProtStatFunc();
    public function __destruct() {}
    var $pubProp2;
    private static $privStatProp;
    use BazTrait;
    public static function pubStatFunc1() {}
    public function pubFunc3() {}
    private $privProp;
    const C1 = 1;
    static function pubStatFunc2() {}
    private function privFunc() {}
    public static $pubStatProp2;
    abstract public static function absPubStatFunc();
    protected function __construct() {}
    const C2 = 2;
    public static function pubStatFunc3() {}
    public $pubProp3;
    protected static function protStatFunc() {}
}
EOT
                ,
            ],
            [
                [
                    'use_trait',
                    'constant',
                    'property_public_static',
                    'property_protected_static',
                    'property_private_static',
                    'property_public',
                    'property_protected',
                    'property_private',
                    'construct',
                    'destruct',
                    'magic',
                    'method_public_static',
                    'method_public_abstract_static',
                    'method_protected_static',
                    'method_protected_abstract_static',
                    'method_private_static',
                    'method_public',
                    'method_public_abstract',
                    'method_protected',
                    'method_protected_abstract',
                    'method_private',
                ],
                <<<'EOT'
<?php

abstract class Foo
{
    use BarTrait;
    use BazTrait;
    const C1 = 1;
    const C2 = 2;
    public static $pubStatProp1;
    public static $pubStatProp2;
    protected static $protStatProp;
    private static $privStatProp;
    public $pubProp1;
    var $pubProp2;
    public $pubProp3;
    protected $protProp;
    private $privProp;
    protected function __construct() {}
    public function __destruct() {}
    public function __toString() {}
    public static function pubStatFunc1() {}
    static function pubStatFunc2() {}
    public static function pubStatFunc3() {}
    abstract public static function absPubStatFunc();
    protected static function protStatFunc() {}
    abstract protected static function absProtStatFunc();
    private static function privStatFunc() {}
    public function pubFunc1() {}
    function pubFunc2() {}
    public function pubFunc3() {}
    abstract public function absPubFunc();
    protected function protFunc() {}
    abstract protected function absProtFunc();
    private function privFunc() {}
}
EOT
                ,
                <<<'EOT'
<?php

abstract class Foo
{
    abstract public function absPubFunc();
    private static function privStatFunc() {}
    protected static $protStatProp;
    public static $pubStatProp1;
    public function pubFunc1() {}
    abstract protected function absProtFunc();
    use BarTrait;
    public $pubProp1;
    public function __toString() {}
    protected function protFunc() {}
    protected $protProp;
    function pubFunc2() {}
    abstract protected static function absProtStatFunc();
    public function __destruct() {}
    var $pubProp2;
    private static $privStatProp;
    use BazTrait;
    public static function pubStatFunc1() {}
    public function pubFunc3() {}
    private $privProp;
    const C1 = 1;
    static function pubStatFunc2() {}
    private function privFunc() {}
    public static $pubStatProp2;
    abstract public static function absPubStatFunc();
    protected function __construct() {}
    const C2 = 2;
    public static function pubStatFunc3() {}
    public $pubProp3;
    protected static function protStatFunc() {}
}
EOT
                ,
            ],
            [
                [
                    'method_public',
                    'method_abstract',
                ],
                <<<'EOT'
<?php

abstract class Foo
{
    public function test2(){}
    public abstract function test1();
}
EOT
                ,
                <<<'EOT'
<?php

abstract class Foo
{
    public abstract function test1();
    public function test2(){}
}
EOT
                ,
            ],
        ];
    }

    /**
     * @param string $input
     * @param string $expected
     *
     * @dataProvider provideSortingConfigurationCases
     */
    public function testFixWithSortingAlgorithm(array $configuration, $expected, $input)
    {
        $this->fixer->configure($configuration);
        $this->doTest($expected, $input);
    }

    public function provideSortingConfigurationCases()
    {
        return [
            [
                [
                    'order' => [
                        'property_public_static',
                        'method_public',
                        'method_private',
                    ],
                    'sort_algorithm' => 'alpha',
                ],
                <<<'EOT'
<?php
class Example
{
    public static $pubStatProp1;
    public static $pubStatProp2;
    public function A(){}
    public function B1(){}
    public function B2(){}
    public function C(){}
    public function C1(){}
    public function D(){}
    private function E(){}
}
EOT
                ,
                <<<'EOT'
<?php
class Example
{
    public function D(){}
    public static $pubStatProp2;
    public function B1(){}
    public function B2(){}
    private function E(){}
    public static $pubStatProp1;
    public function A(){}
    public function C(){}
    public function C1(){}
}
EOT
                ,
            ],
            [
                [
                    'order' => [
                        'use_trait',
                        'constant',
                        'property_public_static',
                        'property_protected_static',
                        'property_private_static',
                        'property_public',
                        'property_protected',
                        'property_private',
                        'construct',
                        'destruct',
                        'magic',
                        'method_public_static',
                        'method_protected_static',
                        'method_private_static',
                        'method_public',
                        'method_protected',
                        'method_private',
                    ],
                    'sort_algorithm' => 'alpha',
                ],
                <<<'EOT'
<?php
class Foo
{
    use BarTrait;
    use BazTrait;
    const C1 = 1;
    const C2 = 2;
    public static $pubStatProp1;
    public static $pubStatProp2;
    protected static $protStatProp;
    private static $privStatProp;
    public $pubProp1;
    var $pubProp2;
    public $pubProp3;
    protected $protProp;
    private $privProp;
    protected function __construct() {}
    public function __destruct() {}
    public function __magicA() {}
    public function __magicB() {}
    public function __toString() {}
    public static function pubStatFunc1() {}
    static function pubStatFunc2() {}
    public static function pubStatFunc3() {
        return $this->privFunc();
    }
    protected static function protStatFunc() {}
    private static function privStatFunc() {}
    public function pubFunc1() {}
    function pubFunc2() {}
    public function pubFunc3(int $b, int $c) {
        $a = $b*$c;

        return $a % 4;
    }
    protected function protFunc() {}
    private function privFunc() {}
}
EOT
                ,
                <<<'EOT'
<?php
class Foo
{
    private static function privStatFunc() {}
    protected static $protStatProp;
    use BazTrait;
    public static $pubStatProp2;
    public $pubProp3;
    use BarTrait;
    public function __toString() {}
    protected function protFunc() {}
    protected $protProp;
    function pubFunc2() {}
    public $pubProp1;
    public function __destruct() {}
    var $pubProp2;
    public function __magicB() {}
    const C2 = 2;
    public static $pubStatProp1;
    public function __magicA() {}
    private static $privStatProp;
    static function pubStatFunc2() {}
    public function pubFunc3(int $b, int $c) {
        $a = $b*$c;

        return $a % 4;
    }
    private $privProp;
    const C1 = 1;
    public static function pubStatFunc3() {
        return $this->privFunc();
    }
    public function pubFunc1() {}
    public static function pubStatFunc1() {}
    private function privFunc() {}
    protected function __construct() {}
    protected static function protStatFunc() {}
}
EOT
                ,
            ],
            [
                [
                    'order' => [
                        'use_trait',
                        'constant',
                        'property_public_static',
                        'property_protected_static',
                        'property_private_static',
                        'property_public',
                        'property_protected',
                        'property_private',
                        'construct',
                        'destruct',
                        'magic',
                        'method_public_static',
                        'method_public_abstract_static',
                        'method_protected_static',
                        'method_protected_abstract_static',
                        'method_private_static',
                        'method_public',
                        'method_public_abstract',
                        'method_protected',
                        'method_protected_abstract',
                        'method_private',
                    ],
                    'sort_algorithm' => 'alpha',
                ],
                <<<'EOT'
<?php
abstract class Foo
{
    use BarTrait;
    use BazTrait;
    const C1 = 1;
    const C2 = 2;
    public static $pubStatProp1;
    public static $pubStatProp2;
    protected static $protStatProp;
    private static $privStatProp;
    public $pubProp1;
    var $pubProp2;
    public $pubProp3;
    protected $protProp;
    private $privProp;
    protected function __construct() {}
    public function __destruct() {}
    public function __magicA() {}
    public function __magicB() {}
    public function __toString() {}
    public static function pubStatFunc1() {}
    static function pubStatFunc2() {}
    public static function pubStatFunc3() {
        return $this->privFunc();
    }
    abstract public static function absPubStatFunc1();
    protected static function protStatFunc() {}
    abstract protected static function absProtStatFunc();
    private static function privStatFunc() {}
    public function pubFunc1() {}
    function pubFunc2() {}
    public function pubFunc3(int $b, int $c) {
        $a = $b*$c;

        return $a % 4;
    }
    abstract public function absPubFunc();
    protected function protFunc() {}
    abstract protected function absProtFunc();
    private function privFunc() {}
}
EOT
                ,
                <<<'EOT'
<?php
abstract class Foo
{
    private static function privStatFunc() {}
    protected static $protStatProp;
    use BazTrait;
    abstract public function absPubFunc();
    public static $pubStatProp2;
    public $pubProp3;
    use BarTrait;
    public function __toString() {}
    protected function protFunc() {}
    protected $protProp;
    function pubFunc2() {}
    public $pubProp1;
    abstract protected static function absProtStatFunc();
    public function __destruct() {}
    var $pubProp2;
    public function __magicB() {}
    const C2 = 2;
    public static $pubStatProp1;
    public function __magicA() {}
    private static $privStatProp;
    static function pubStatFunc2() {}
    public function pubFunc3(int $b, int $c) {
        $a = $b*$c;

        return $a % 4;
    }
    private $privProp;
    const C1 = 1;
    abstract protected function absProtFunc();
    public static function pubStatFunc3() {
        return $this->privFunc();
    }
    public function pubFunc1() {}
    public static function pubStatFunc1() {}
    private function privFunc() {}
    protected function __construct() {}
    protected static function protStatFunc() {}
    abstract public static function absPubStatFunc1();
}
EOT
                ,
            ],
        ];
    }

    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideFix74Cases
     * @requires PHP 7.4
     */
    public function testFix74($expected, $input = null, array $configuration = null)
    {
        if (null !== $configuration) {
            $this->fixer->configure($configuration);
        }

        $this->doTest($expected, $input);
    }

    public function provideFix74Cases()
    {
        yield [
            '<?php
            class Foo {
                public iterable $baz;
                var ? Foo\Bar $qux;
                protected string $bar;
                private ?int $foo;
            }',
            '<?php
            class Foo {
                private ?int $foo;
                protected string $bar;
                public iterable $baz;
                var ? Foo\Bar $qux;
            }',
        ];
        yield [
            '<?php
            class Foo {
                public string $bar;
                public array $baz;
                public ?int $foo;
                public ? Foo\Bar $qux;
            }',
            '<?php
            class Foo {
                public array $baz;
                public ? Foo\Bar $qux;
                public string $bar;
                public ?int $foo;
            }',
            [
                'sort_algorithm' => 'alpha',
            ],
        ];
    }

    public function testWrongConfig()
    {
        $this->expectException(\PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException::class);
        $this->expectExceptionMessageMatches('/^\[ordered_class_elements\] Invalid configuration: The option "order" .*\.$/');

        $this->fixer->configure(['order' => ['foo']]);
    }

    /**
     * @param string $methodName1
     * @param string $methodName2
     *
     * @dataProvider provideWithConfigWithNoCandidateCases
     */
    public function testWithConfigWithNoCandidate($methodName1, $methodName2)
    {
        $template = '<?php
class TestClass
{
    public function %s(){}
    public function %s(){}
}';
        $this->fixer->configure(['order' => ['use_trait'], 'sort_algorithm' => 'alpha']);

        $this->doTest(
            sprintf($template, $methodName2, $methodName1),
            sprintf($template, $methodName1, $methodName2)
        );
    }

    public function provideWithConfigWithNoCandidateCases()
    {
        yield ['z', '__construct'];
        yield ['z', '__destruct'];
        yield ['z', '__sleep'];
        yield ['z', 'abc'];
    }

    /**
     * @param string $expected
     * @param string $input
     *
     * @dataProvider provideFix80Cases
     * @requires PHP 8.0
     */
    public function testFix80($expected, $input)
    {
        $this->doTest($expected, $input);
    }

    public function provideFix80Cases()
    {
        yield [
            '<?php class Foo
            {

                #[PublicBarAttribute0]
                public int $bar = 1;

                #[PublicAttribute0]
                #[PublicAttribute1]
                #[PublicAttribute2]
                public function fooPublic()
                {
                }
                #[PrivateAttribute0]
                private function fooPrivate()
                {
                }
            }',
            '<?php class Foo
            {
                #[PrivateAttribute0]
                private function fooPrivate()
                {
                }

                #[PublicBarAttribute0]
                public int $bar = 1;

                #[PublicAttribute0]
                #[PublicAttribute1]
                #[PublicAttribute2]
                public function fooPublic()
                {
                }
            }',
        ];
    }
}
