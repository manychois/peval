# PEval - PHP Expression Evaluator

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-777bb3.svg)](https://www.php.net/releases/8.4/en.php)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A powerful PHP library for parsing and evaluating expressions with support for variables, functions, operators, and complex data structures. PEval provides a safe and controlled environment for executing dynamic expressions at runtime without the risks associated with `eval()`.

## Features

| Category | Description | Examples |
|----------|-------------|----------|
| **🔢 Arithmetic** | Arithmetic operations | `+`, `-`, `*`, `/`, `%`, `**`, `()` |
| **🔍 Comparison** | Value and type comparison operators | `==`, `!=`, `===`, `!==`, `<`, `<=`, `>`, `>=`, `<=>` |
| **🧠 Logical** | Boolean logic operations | `&&`, `\|\|`, `!`, `and`, `or`, `xor` |
| **📚 Arrays** | Array creation and access operations | `[1, 2, 3]`, `$arr[0]`, `['a' => 'Apple']` |
| **🔤 String** | String manipulation and interpolation | `'abc'`, `"Hello {$name}!"`, `.` |
| **📝 Variables** | Dynamic variable resolution with context | `$variable` |
| **🔑 Object Access** | Object properties and constants | `$obj->prop`, `Class::CONST` |
| **📞 Function Calls** | Function and object method calls | `func()`, `$obj->method()` |
| **❓ Others** | Ternary and null coalescing | `? :`, `??` |

## Installation

```bash
composer require manychois/peval
```
## Requirements

- PHP 8.4 or higher
- No external dependencies, requires `php-tokenizer` which is a built-in extension

## Quick Start

```php
<?php
use Manychois\Peval\Parser;
use Manychois\Peval\Evaluator;

// Create parser and evaluator
$parser = new Parser();
$evaluator = new Evaluator([
    'name' => 'World',
    'price' => 123,
    'items' => ['apple', 'banana', 'cherry']
]);

// Parse and evaluate expressions
$expression = $parser->parse('"Hello {$name}!"');
echo $evaluator->evaluate($expression); // "Hello World!"

$expression = $parser->parse('100 + $price * 1.5');
echo $evaluator->evaluate($expression); // 284.5

$expression = $parser->parse('$items[1]');
echo $evaluator->evaluate($expression); // "banana"
```
## Comparison with Other Libraries

### symfony/expression-language

The `symfony/expression-language` library provides rich expression evaluation capabilities but it has a  different syntax than PHP. `manychois/peval` understands native PHP syntax, making it easier for PHP developers to use without learning a new syntax.

### nikic/php-parser

The `nikic/php-parser` library is primarily a parser for PHP code, not specifically designed for expression evaluation. It can parse PHP code into an AST (Abstract Syntax Tree), but it does not provide a built-in evaluator. `manychois/peval` builds on top of the PHP tokenizer to provide a complete solution for parsing and evaluating expressions. Also, it focuses on expressions rather than full PHP syntax, making it more lightweight and easier to use.

### madorin/matex

The `madorin/matex` library focuses mainly on mathematical expressions and does not support complex data structures like arrays or objects. It is limited to numeric operations, while `manychois/peval` supports a wide range of PHP features including strings, arrays, and objects.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
