<?php

declare(strict_types=1);

namespace Manychois\Peval\Tokenisation;

enum TokenType: string
{
    // Literals
    case BOOL = 'BOOL';
    case INTEGER = 'INTEGER';
    case FLOAT = 'FLOAT';
    case STRING = 'STRING';
    case NULL = 'NULL';

    // Operators
    case PLUS = '+';
    case MINUS = '-';
    case MULTIPLY = '*';
    case DIVIDE = '/';
    case MODULO = '%';
    case POWER = '**';

    // Comparison
    case EQUAL = '==';
    case NOT_EQUAL = '!=';
    case IDENTICAL = '===';
    case NOT_IDENTICAL = '!==';
    case LESS = '<';
    case LESS_EQUAL = '<=';
    case GREATER = '>';
    case GREATER_EQUAL = '>=';

    // Logical
    case SYMBOL_AND = '&&';
    case SYMBOL_OR = '||';
    case WORD_AND = 'AND';
    case WORD_OR = 'OR';
    case NOT = '!';
    case XOR = 'XOR';

    // Keywords
    case ARRAY = 'ARRAY';

    // Other
    case VARIABLE = 'VARIABLE';
    case IDENTIFIER = 'IDENTIFIER';
    case LEFT_PARENTHESIS = '(';
    case RIGHT_PARENTHESIS = ')';
    case LEFT_BRACKET = '[';
    case RIGHT_BRACKET = ']';
    case LEFT_BRACE = '{';
    case RIGHT_BRACE = '}';
    case COMMA = ',';
    case DOT = '.';
    case QUESTION = '?';
    case COLON = ':';
    case QUOTE = '"';
    case DOUBLE_ARROW = '=>';
    case WHITESPACE = ' ';
    case EOF = 'EOF';
}
