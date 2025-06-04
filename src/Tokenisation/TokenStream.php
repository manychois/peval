<?php

declare(strict_types=1);

namespace Manychois\Peval\Tokenisation;

use LogicException;
use Manychois\Peval\ExpressionParseException;
use PhpToken;

class TokenStream
{
    public readonly int $length;
    public int $current = 0;

    /**
     * @var Token[]
     */
    private array $tokens = [];

    public function __construct(string $source)
    {
        $linePosLookup = [0];
        $offset = 0;
        while (true) {
            $linePos = strpos($source, "\n", $offset);
            if (false === $linePos) {
                break;
            }
            $linePosLookup[] = $linePos;
            $offset = $linePos + 1;
        }

        $phpTokens = PhpToken::tokenize("<?php\n" . $source);
        array_shift($phpTokens);
        foreach ($phpTokens as $phpToken) {
            $tokenType = self::getTokenType($phpToken);
            if (TokenType::WHITESPACE === $tokenType) {
                continue;
            }
            $this->tokens[] = new Token(
                $tokenType,
                $phpToken->text,
                $phpToken->pos - 6,
                $phpToken->line - 1,
                $phpToken->pos - $linePosLookup[$phpToken->line - 2] - 5
            );
        }

        $this->length = count($this->tokens);
    }

    public function advance(): Token
    {
        $current = $this->current();
        ++$this->current;

        return $current;
    }

    public function createParseException(string $message = ''): ExpressionParseException
    {
        $current = $this->current();
        if ('' === $message) {
            $message = sprintf('Unexpected token "%s"', $current->text);
        }
        $message .= sprintf(' at line %d, column %d', $current->line, $current->column);

        return new ExpressionParseException($message);
    }

    public function current(): Token
    {
        if ($this->isEof()) {
            throw new ExpressionParseException('Unexpected end of input');
        }

        return $this->tokens[$this->current];
    }

    public function isEof(): bool
    {
        return $this->current >= $this->length;
    }

    /**
     * @phpstan-impure
     */
    public function matchAny(TokenType ...$types): bool
    {
        if ($this->isEof()) {
            return false;
        }
        $currentToken = $this->current();
        foreach ($types as $type) {
            if ($currentToken->type === $type) {
                ++$this->current;

                return true;
            }
        }

        return false;
    }

    public function previous(): Token
    {
        if ($this->current <= 0) {
            throw new LogicException('No previous token available');
        }

        return $this->tokens[$this->current - 1];
    }

    private static function getTokenType(PhpToken $phpToken): TokenType
    {
        $tokenType = match ($phpToken->id) {
            \T_ARRAY => TokenType::ARRAY,
            \T_BOOLEAN_AND => TokenType::WORD_AND,
            \T_BOOLEAN_OR => TokenType::WORD_OR,
            \T_CONSTANT_ENCAPSED_STRING, \T_ENCAPSED_AND_WHITESPACE => TokenType::STRING,
            \T_CURLY_OPEN => TokenType::LEFT_BRACE,
            \T_DOUBLE_ARROW => TokenType::DOUBLE_ARROW,
            \T_DOUBLE_COLON => TokenType::DOUBLE_COLON,
            \T_DNUMBER => TokenType::FLOAT,
            \T_IS_EQUAL => TokenType::EQUAL,
            \T_IS_GREATER_OR_EQUAL => TokenType::GREATER_EQUAL,
            \T_IS_IDENTICAL => TokenType::IDENTICAL,
            \T_IS_NOT_EQUAL => TokenType::NOT_EQUAL,
            \T_IS_NOT_IDENTICAL => TokenType::NOT_IDENTICAL,
            \T_IS_SMALLER_OR_EQUAL => TokenType::LESS_EQUAL,
            \T_LNUMBER => TokenType::INTEGER,
            \T_LOGICAL_AND => TokenType::SYMBOL_AND,
            \T_LOGICAL_OR => TokenType::SYMBOL_OR,
            \T_LOGICAL_XOR => TokenType::XOR,
            \T_OBJECT_OPERATOR => TokenType::ARROW,
            \T_POW => TokenType::POWER,
            \T_VARIABLE => TokenType::VARIABLE,
            \T_WHITESPACE => TokenType::WHITESPACE,
            default => null,
        };
        if (null === $tokenType) {
            $tokenType = match ($phpToken->text) {
                '+' => TokenType::PLUS,
                '-' => TokenType::MINUS,
                '*' => TokenType::MULTIPLY,
                '/' => TokenType::DIVIDE,
                '%' => TokenType::MODULO,
                '!' => TokenType::NOT,
                '(' => TokenType::LEFT_PARENTHESIS,
                ')' => TokenType::RIGHT_PARENTHESIS,
                '>' => TokenType::GREATER,
                '<' => TokenType::LESS,
                '.' => TokenType::DOT,
                '"' => TokenType::QUOTE,
                '}' => TokenType::RIGHT_BRACE,
                '[' => TokenType::LEFT_BRACKET,
                ']' => TokenType::RIGHT_BRACKET,
                ',' => TokenType::COMMA,
                default => null,
            };
        }
        if (null === $tokenType && T_STRING === $phpToken->id) {
            $lower = strtolower($phpToken->text);
            $tokenType = match ($lower) {
                'true', 'false' => TokenType::BOOL,
                'null' => TokenType::NULL,
                default => TokenType::IDENTIFIER,
            };
        }

        if (null === $tokenType) {
            throw new LogicException(sprintf('Unhandled PHP Token %s.', $phpToken->id < 256 ? $phpToken->text : \token_name($phpToken->id)));
        }

        return $tokenType;
    }
}
