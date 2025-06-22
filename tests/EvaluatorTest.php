<?php

declare(strict_types=1);

namespace Manychois\PevalTests;

use DateTime;
use DateTimeZone;
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
    private Evaluator $evaluator;
    private array $context;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new Evaluator();
        $this->context = [
            'a' => 12,
            'b' => 34,
            'c' => 'Apple',
            'd' => 'Banana',
            'e' => new DateTime('1997-07-01', new DateTimeZone('UTC')),
            'f' => [1, 2, 3],
        ];
    }

    #[DataProvider('provideEvaluate')]
    public function testEvaluate(string $php, mixed $expected): void
    {
        $parser = new Parser();
        $expr = $parser->parse($php);
        $result = $this->evaluator->evaluate($expr, $this->context);
        $this->assertSame($expected, $result);
    }

    public static function provideEvaluate(): iterable
    {
        yield 'constant' => ['12345', 12345];
        yield 'add' => ['$a + $b', 46];
        yield 'subtract' => ['$b - $a', 22];
        yield 'multiply' => ['$a * $b', 408];
        yield 'divide' => ['round($a / $b, 4)', 0.3529];
        yield 'string concat' => ['$c . $d', 'AppleBanana'];
        yield 'ternary true' => ['$a > 10 ? $c : $d', 'Apple'];
        yield 'ternary false' => ['$a < 10 ? $c : $d', 'Banana'];
        yield 'string interpolation 1' => ['"Value: $a"', 'Value: 12'];
        yield 'string interpolation 2' => ['"Value: {$a}"', 'Value: 12'];
        yield 'method call' => ['$e->format("Y-m-d")', '1997-07-01'];
        yield 'object class' => ['$e::class', 'DateTime'];
        yield 'object constant' => ['$e::ATOM', 'Y-m-d\TH:i:sP'];
        yield 'arrow function' => ['array_map(fn($x) => $x * 2, $f)', [2, 4, 6]];
        yield 'string cast' => ['(string)$a', '12'];
        yield 'instanceof' => ['$e instanceof DateTime', true];
        yield 'array access' => ['$f[1]', 2];
        yield 'array creation' => ['array(1, 2, 3)', [1, 2, 3]];
    }
}
