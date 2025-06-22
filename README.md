# Peval - PHP Expression Evaluator

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-777bb3.svg)](https://www.php.net/releases/8.4/en.php)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

Peval is a secure PHP library for parsing and evaluating expressions with support for variables, functions, operators, objects and array structures. Built on top of the excellent [`nikic/php-parser`](https://github.com/nikic/PHP-Parser) library, Peval provides a safe and controlled environment for executing dynamic expressions at runtime without the risks associated with `eval()`. All unsafe PHP functions are strictly prohibited to ensure security.

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
| **➡️ Arrow Functions** | Arrow function syntax for concise callbacks | `array_map(fn($x) => $x * 2, $array)` |
| **🔒 Security** | Unsafe functions prohibited | No `eval()`, `exec()`, file operations, etc. |
| **❓ Others** | Ternary and null coalescing | `? :`, `??` |

## Installation

```bash
composer require manychois/peval
```
## Requirements

- PHP 8.4 or higher

## Security

Peval prioritizes security by maintaining a comprehensive blacklist of unsafe PHP functions that are prohibited from execution. This includes:

- **Code execution functions**: `eval()`, `exec()`, `system()`, etc.
- **File system operations**: `file_get_contents()`, `unlink()`, `chmod()`, etc.
- **Network functions**: `curl_exec()`, `mail()`, etc.
- **Reflection capabilities**: `get_defined_functions()`, `class_exists()`, etc.

This ensures that expressions can only perform safe computations without accessing external resources or executing potentially dangerous operations.

For a complete list of prohibited functions, please refer to the [src/unsafe.php](src/unsafe.php) file in the source code.

## Quick Start

```php
<?php
use Manychois\Peval\Parser;
use Manychois\Peval\Evaluator;

// Create parser and evaluator
$parser = new Parser();
$evaluator = new Evaluator();
$expression = $parser->parse('"Hello {$name}!"');
$context = [
    'name' => 'World',
];
echo $evaluator->evaluate($expression, $context); // print "Hello World!"
```
## Comparison with Other Libraries

### symfony/expression-language

The `symfony/expression-language` library provides rich expression evaluation capabilities but it has a  different syntax than PHP. `manychois/Peval` understands native PHP syntax, making it easier for PHP developers to use without learning a new syntax.

### nikic/php-parser

The `nikic/php-parser` library is primarily a parser for PHP code. Its built-in evaluator `ConstExprEvaluator` can only handle constant expressions. `manychois/Peval` builds on top of it to provide a complete solution for evaluating expressions.

### madorin/matex

The `madorin/matex` library focuses mainly on mathematical expressions and does not support complex data structures like arrays or objects. It is limited to numeric operations, while `manychois/Peval` supports a wide range of PHP features including strings, arrays, and objects.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
