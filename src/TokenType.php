<?php

declare(strict_types=1);

namespace Manychois\Peval;

enum TokenType: string
{
    // Literals
    case INTEGER = 'INTEGER';
    case FLOAT = 'FLOAT';
    case STRING = 'STRING';
    case TRUE = 'TRUE';
    case FALSE = 'FALSE';
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
    case AND = '&&';
    case OR = '||';
    case NOT = '!';

    // Other
    case VARIABLE = 'VARIABLE';
    case IDENTIFIER = 'IDENTIFIER';
    case LEFT_PARENTHESIS = '(';
    case RIGHT_PARENTHESIS = ')';
    case LEFT_BRACKET = '[';
    case RIGHT_BRACKET = ']';
    case COMMA = ',';
    case DOT = '.';
    case QUESTION = '?';
    case COLON = ':';
    case EOF = 'EOF';
}
