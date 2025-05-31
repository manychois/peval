<?php

declare(strict_types=1);

namespace Manychois\Peval\Tokenisation;

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

        $phpTokens = \PhpToken::tokenize("<?php\n".$source);
        array_shift($phpTokens);
        foreach ($phpTokens as $phpToken) {
            // TODO: remove this debug output in production code
            printf(
                "[%s] %s at (%d,%d)\n",
                str_replace("\n", '⏎', $phpToken->text),
                token_name($phpToken->id),
                $phpToken->line,
                $phpToken->pos
            );

            $tokenType = static::getTokenType($phpToken);
            if (null === $tokenType) {
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

    public function createParseError(string $message = ''): \ParseError
    {
        $current = $this->current();
        if ('' === $message) {
            $message = sprintf('Unexpected token "%s"', $current->text);
        }
        $message .= sprintf(' at line %d, column %d', $current->line, $current->column);

        return new \ParseError($message);
    }

    public function current(): Token
    {
        if ($this->isEof()) {
            throw new \ParseError('Unexpected end of input');
        }

        return $this->tokens[$this->current];
    }

    public function isEof(): bool
    {
        return $this->current >= $this->length;
    }

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
            throw new \LogicException('No previous token available');
        }

        return $this->tokens[$this->current - 1];
    }

    private static function getTokenType(\PhpToken $phpToken): ?TokenType
    {
        $tokenType = match ($phpToken->id) {
            T_BOOLEAN_AND => TokenType::WORD_AND,
            T_BOOLEAN_OR => TokenType::WORD_OR,
            T_DNUMBER => TokenType::FLOAT,
            T_IS_EQUAL => TokenType::EQUAL,
            T_IS_GREATER_OR_EQUAL => TokenType::GREATER_EQUAL,
            T_IS_IDENTICAL => TokenType::IDENTICAL,
            T_IS_NOT_EQUAL => TokenType::NOT_EQUAL,
            T_IS_NOT_IDENTICAL => TokenType::NOT_IDENTICAL,
            T_IS_SMALLER_OR_EQUAL => TokenType::LESS_EQUAL,
            T_LNUMBER => TokenType::INTEGER,
            T_LOGICAL_AND => TokenType::SYMBOL_AND,
            T_LOGICAL_OR => TokenType::SYMBOL_OR,
            T_LOGICAL_XOR => TokenType::XOR,
            T_POW => TokenType::POWER,
            T_VARIABLE => TokenType::VARIABLE,
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
                default => null,
            };
        }
        if (null === $tokenType && T_STRING === $phpToken->id) {
            $lower = strtolower($phpToken->text);
            $tokenType = match ($lower) {
                'true', 'false' => TokenType::BOOL,
                default => null,
            };
        }

        return $tokenType;
    }
}
