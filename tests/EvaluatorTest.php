<?php

declare(strict_types=1);

namespace Manychois\PevalTests;

use DateTime;
use Generator;
use LogicException;
use Manychois\Peval\Evaluator;
use Manychois\Peval\Parser;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 *
 * @coversNothing
 */
class EvaluatorTest extends AbstractBaseTestCase
{
    #[DataProvider('provideEvaluateData')]
    public function testEvaluate(string $expression, array $context, mixed $expected): void
    {
        $evaluator = new Evaluator($context);
        $expression = (new Parser())->parse($expression);
        $result = $evaluator->evaluate($expression);
        $this->assertEquals($expected, $result);
    }

    public static function provideEvaluateData(): Generator
    {
        yield 'unary1' => ['-1', [], -1];
        yield 'unary2' => ['!$a', ['a' => true], false];
        yield 'unary3' => ['+$a', ['a' => '12345'], 12345];
        yield 'binary1' => ['1 + 2 * 3 - 4', [], 3];
        yield 'binary2' => ['2 ** 4 / 4', [], 4];
        yield 'binary3' => ['$a . \' \' . $b', ['a' => 'Apple', 'b' => 'juice'], 'Apple juice'];
        yield 'comparison1' => ['$a == $b', ['a' => 1, 'b' => '1'], true];
        yield 'comparison2' => ['$a === $b', ['a' => 1, 'b' => '1'], false];
        yield 'comparison3' => ['$a != $b', ['a' => 1, 'b' => '1'], false];
        yield 'comparison4' => ['$a !== $b', ['a' => 1, 'b' => '1'], true];
        yield 'comparison5' => ['$a < $b', ['a' => 1, 'b' => '2'], true];
        yield 'comparison6' => ['$a <= $b', ['a' => 1, 'b' => '1'], true];
        yield 'comparison7' => ['$a > $b', ['a' => 2, 'b' => '1'], true];
        yield 'comparison8' => ['$a >= $b', ['a' => 1, 'b' => '1'], true];
        yield 'logical1' => ['$a && $b', ['a' => true, 'b' => false], false];
        yield 'logical2' => ['$a || $b', ['a' => false, 'b' => true], true];
        yield 'logical3' => ['$a and $b', ['a' => true, 'b' => false], false];
        yield 'logical4' => ['$a or $b', ['a' => false, 'b' => true], true];
        yield 'logical5' => ['$a xor $b', ['a' => true, 'b' => true], false];
        yield 'string1' => ["'It\\\\\\'s'", [], 'It\\\'s'];
        yield 'string2' => ['"\"Quote\""', [], '"Quote"'];
        yield 'string3' => ['"Special chars:\n\r\t\v\e\f\\\"', [], "Special chars:\n\r\t\v\e\f\\"];
        yield 'string4' => ['"Octal: \101\102\103"', [], 'Octal: ABC'];
        yield 'string5' => ['"Hex: \x41\x42\x43"', [], 'Hex: ABC'];
        yield 'string6' => ['"Unicode: \u{41}\u{42}\u{43}"', [], 'Unicode: ABC'];
        yield 'interpolation1' => ['"Hello {$name}!"', ['name' => 'World'], 'Hello World!'];
        yield 'interpolation2' => ['"Second element is {$items[1]}."', ['items' => [11, 22, 33]], 'Second element is 22.'];
        yield 'array1' => ['["a", "b", "c"][2]', [], 'c'];
        yield 'property1' => ['$obj::ATOM', ['obj' => new DateTime()], 'Y-m-d\TH:i:sP'];
    }

    #[DataProvider('provideEvaluateThrowExData')]
    public function testEvaluateThrowEx(string $expression, array $context, string $expectedMsg): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($expectedMsg);

        $evaluator = new Evaluator($context);
        $expression = (new Parser())->parse($expression);
        $evaluator->evaluate($expression);
    }

    public static function provideEvaluateThrowExData(): Generator
    {
        yield 'unary1' => ['-$a', ['a' => []], 'Invalid operand for unary operator at line 1, column 1, found array'];
        yield 'binary1' => ['1 + $a', ['a' => []], 'Invalid right operand for mathematical operator at line 1, column 3, found array'];
        yield 'binary2' => ['$a . $b', ['a' => [], 'b' => 'abc'], 'Invalid left operand for concatenation operator at line 1, column 4, found array'];
        yield 'variable1' => ['$a', [], 'Undefined variable: $a'];
        yield 'string1' => ['"Invalid escape: \u{FFFFFF}"', [], 'Invalid Unicode escape sequence: FFFFFF'];
        yield 'array1' => ['["a", "b", "c"][3]', [], 'Undefined offset 3 in array'];
    }
}
