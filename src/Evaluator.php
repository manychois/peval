<?php

declare(strict_types=1);

namespace Manychois\Peval;

use Closure;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\PrettyPrinter\Standard as Printer;
use Stringable;
use Throwable;
use WeakReference;

/**
 * Evaluator class for evaluating PHP expressions in a given context.
 */
class Evaluator
{
    /**
     * @var WeakReference<Printer>|null
     */
    private static ?WeakReference $printerRef = null;
    /**
     * @var array<string>
     */
    private static array $unsafeFunctions = [];
    private readonly ConstExprEvaluator $inner;
    /**
     * @var array<string,mixed>
     */
    private array $context = [];

    /**
     * Constructor initializes the evaluator.
     */
    public function __construct()
    {
        $closure = Closure::fromCallable([$this, 'fallbackEvaluate']);
        $this->inner = new ConstExprEvaluator($closure);
    }

    /**
     * Set the unsafe functions that should not be allowed during evaluation.
     *
     * @param array<string> $list List of unsafe function names
     */
    public static function setUnsafeFunctions(array $list): void
    {
        self::$unsafeFunctions = $list;
    }

    protected static function getPrinter(): Printer
    {
        $printer = self::$printerRef?->get();
        if (!($printer instanceof Printer)) {
            $printer = new Printer();
            self::$printerRef = WeakReference::create($printer);
        }

        return $printer;
    }

    protected static function getUnsafeFunctions(): array
    {
        if (count(self::$unsafeFunctions) === 0) {
            self::$unsafeFunctions = include __DIR__ . '/unsafe.php';
        }

        return self::$unsafeFunctions;
    }

    /**
     * Evaluate a given expression in the context of provided variables.
     *
     * @param Expr                $expr    the expression to evaluate
     * @param array<string,mixed> $context the context for variable evaluation, where keys are variable names and values are their corresponding values
     *
     * @return mixed the result of the evaluation
     */
    public function evaluate(Expr $expr, array $context): mixed
    {
        try {
            $this->context = $context;

            return $this->inner->evaluateSilently($expr);
        } catch (Throwable $ex) {
            throw new EvaluationException($ex->getMessage(), $ex->getCode(), $ex);
        } finally {
            $this->context = []; // Clear context after evaluation
        }
    }

    protected function fallbackEvaluate(Expr $expr): mixed
    {
        return match ($expr::class) {
            Expr\ArrowFunction::class => $this->evalArrowFunction($expr),
            Expr\Assign::class => $this->throwAssignError($expr),
            Expr\Cast\Array_::class,
            Expr\Cast\Bool_::class,
            Expr\Cast\Double::class,
            Expr\Cast\Int_::class,
            Expr\Cast\Object_::class,
            Expr\Cast\String_::class => $this->evalCast($expr, $expr::class),
            Expr\ClassConstFetch::class => $this->evalClassConstFetch($expr),
            Expr\FuncCall::class => $this->evalFuncCall($expr),
            Expr\Instanceof_::class => $this->evalInstanceOf($expr),
            Expr\MethodCall::class => $this->evalMethodCall($expr),
            Expr\Variable::class => $this->evalVariable($expr),
            Scalar\InterpolatedString::class => $this->evalInterpolatedString($expr),
            default => throw new EvaluationException(sprintf('Expression %s of type %s cannot be evaluated', self::getPrinter()->prettyPrintExpr($expr), $expr::class)),
        };
    }

    /**
     * @param array<Node\Arg|Node\VariadicPlaceholder> $args
     *
     * @return array<int,mixed>
     */
    protected function evalArgs(array $args): array
    {
        $values = [];
        foreach ($args as $arg) {
            if ($arg instanceof Node\Arg) {
                if ($arg->byRef) {
                    throw new EvaluationException(sprintf('Arguments passed by reference %s is not supported', self::getPrinter()->prettyPrintExpr($arg->value)));
                }
            } else {
                throw new EvaluationException('VariadicPlaceholder is not supported');
            }
            $values[] = $this->inner->evaluateSilently($arg->value);
        }

        return $values;
    }

    protected function evalArrowFunction(Expr $expr): Closure
    {
        assert($expr instanceof Expr\ArrowFunction, 'Expected ArrowFunction expression');
        $evaluator = new self();
        $paramNames = [];
        foreach ($expr->params as $param) {
            assert($param->var instanceof Expr\Variable, 'Expected Variable in ArrowFunction parameter');
            $paramName = $param->var->name;
            assert(is_string($paramName));
            $paramNames[] = $paramName;
        }

        return function (...$args) use ($evaluator, $expr, $paramNames) {
            $context = $this->context;
            foreach ($args as $i => $arg) {
                $context[$paramNames[$i]] = $arg;
            }

            return $evaluator->evaluate($expr->expr, $context);
        };
    }

    protected function evalCast(Expr $expr, string $castType): mixed
    {
        assert($expr instanceof Expr\Cast, 'Expected Cast expression');
        $value = $this->inner->evaluateSilently($expr->expr);

        $expectsScalar = match ($castType) {
            Expr\Cast\Bool_::class,
            Expr\Cast\Double::class,
            Expr\Cast\Int_::class,
            Expr\Cast\String_::class => true,
            default => false,
        };
        if ($expectsScalar) {
            if (!is_scalar($value)) {
                throw new EvaluationException(sprintf('Cannot cast %s to %s', get_debug_type($value), $castType));
            }

            return match ($castType) {
                Expr\Cast\Bool_::class => (bool) $value,
                Expr\Cast\Double::class => (float) $value,
                Expr\Cast\Int_::class => (int) $value,
                Expr\Cast\String_::class => (string) $value,
                default => throw new EvaluationException(sprintf('Unsupported cast type: %s', $castType)),
            };
        }

        return match ($castType) {
            Expr\Cast\Array_::class => (array) $value,
            Expr\Cast\Object_::class => (object) $value,
            default => throw new EvaluationException(sprintf('Unsupported cast type: %s', $castType)),
        };
    }

    protected function evalClassConstFetch(Expr $expr): mixed
    {
        assert($expr instanceof Expr\ClassConstFetch, 'Expected ClassConstFetch expression');
        if ($expr->class instanceof Node\Name) {
            $className = $expr->class->name;
        } else {
            $evaluated = $this->inner->evaluateSilently($expr->class);
            if (!is_object($evaluated)) {
                throw new EvaluationException(sprintf('Class name %s must be a string or an object, got %s', self::getPrinter()->prettyPrintExpr($expr->class), get_debug_type($evaluated)));
            }
            $className = $evaluated::class;
        }
        $constName = $expr->name;
        if ($constName instanceof Node\Identifier) {
            $constName = $constName->name;
        } elseif ($constName instanceof Expr) {
            $evaluated = $this->inner->evaluateSilently($constName);
            if (!is_string($evaluated)) {
                throw new EvaluationException(sprintf('Constant name %s must be a string, got %s', self::getPrinter()->prettyPrintExpr($constName), get_debug_type($evaluated)));
            }
            $constName = $evaluated;
        } else {
            throw new EvaluationException(sprintf('Unexpected constant name type: %s', get_debug_type($constName)));
        }

        if ('class' === $constName) {
            return $className;
        }

        if (!class_exists($className) || !defined("{$className}::{$constName}")) {
            throw new EvaluationException(sprintf('Constant %s does not exist in class %s', $constName, $className));
        }

        return constant("{$className}::{$constName}");
    }

    protected function evalFuncCall(Expr $expr): mixed
    {
        assert($expr instanceof Expr\FuncCall, 'Expected FuncCall expression');
        if ($expr->name instanceof Node\Name) {
            $funcName = $expr->name->name;
        } else {
            $funcName = $this->inner->evaluateSilently($expr->name);
            if (!is_string($funcName)) {
                throw new EvaluationException(sprintf('Function name %s must be a string, got %s', self::getPrinter()->prettyPrintExpr($expr->name), get_debug_type($funcName)));
            }
        }

        if (in_array($funcName, self::getUnsafeFunctions(), true)) {
            throw new EvaluationException(sprintf('Function %s is not allowed', $funcName));
        }

        if (!function_exists($funcName)) {
            throw new EvaluationException(sprintf('Function %s does not exist', $funcName));
        }

        $argValues = $this->evalArgs($expr->args);

        return call_user_func_array($funcName, $argValues);
    }

    protected function evalInstanceOf(Expr $expr): bool
    {
        assert($expr instanceof Expr\Instanceof_, 'Expected Instanceof_ expression');
        $object = $this->inner->evaluateSilently($expr->expr);
        if (!is_object($object)) {
            return false;
        }

        assert($expr->class instanceof Node\Name, 'Expected class to be a Name in Instanceof_ expression');
        $className = $expr->class->name;

        return $object instanceof $className;
    }

    protected function evalInterpolatedString(Expr $expr): mixed
    {
        assert($expr instanceof Scalar\InterpolatedString, 'Expected InterpolatedString expression');
        $result = '';
        foreach ($expr->parts as $part) {
            if ($part instanceof Node\InterpolatedStringPart) {
                $result .= $part->value;
            } else {
                $value = $this->inner->evaluateSilently($part);
                if (is_scalar($value)) {
                    $result .= strval($value);
                } elseif ($value instanceof Stringable) {
                    $result .= $value->__toString();
                } else {
                    throw new EvaluationException(sprintf('Cannot convert %s to string', self::getPrinter()->prettyPrintExpr($part)));
                }
            }
        }

        return $result;
    }

    protected function evalMethodCall(Expr $expr): mixed
    {
        assert($expr instanceof Expr\MethodCall, 'Expected MethodCall expression');
        $object = $this->inner->evaluateSilently($expr->var);
        if (!is_object($object)) {
            throw new EvaluationException(sprintf('Method call on non-object: %s', self::getPrinter()->prettyPrintExpr($expr->var)));
        }

        if ($expr->name instanceof Node\Identifier) {
            $methodName = $expr->name->name;
        } else {
            $methodName = $this->inner->evaluateSilently($expr->name);
            if (!is_string($methodName)) {
                throw new EvaluationException(sprintf('Method name must be a string, got %s', get_debug_type($methodName)));
            }
        }

        if (!method_exists($object, $methodName)) {
            throw new EvaluationException(sprintf('Method %s does not exist on object of type %s', $methodName, get_debug_type($object)));
        }

        $argValues = $this->evalArgs($expr->args);
        $callable = [$object, $methodName];
        assert(is_callable($callable), 'Method call is not callable');

        return call_user_func_array($callable, $argValues);
    }

    protected function evalVariable(Expr $expr): mixed
    {
        assert($expr instanceof Expr\Variable, 'Expected Variable expression');
        if (is_string($expr->name)) {
            $varName = $expr->name;
        } else {
            $varName = $this->inner->evaluateSilently($expr->name);
        }

        if (!isset($this->context[$varName])) {
            throw new EvaluationException(sprintf('Variable is not defined in the context: %s', self::getPrinter()->prettyPrintExpr($expr)));
        }

        return $this->context[$varName];
    }

    protected function throwAssignError(Expr $expr): never
    {
        assert($expr instanceof Expr\Assign, 'Expected Assign expression');
        throw new EvaluationException(sprintf('Assignment expressions are not supported: %s', self::getPrinter()->prettyPrintExpr($expr)));
    }
}
