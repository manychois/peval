<?php

declare(strict_types=1);

namespace Manychois\Peval;

class LexerProgress
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

        $rawTokens = \PhpToken::tokenize("<?php\n".$source);
        array_shift($rawTokens);
        foreach ($rawTokens as $rawToken) {
            printf(
                "[%s] %s at (%d,%d)\n",
                str_replace("\n", '⏎', $rawToken->text),
                token_name($rawToken->id),
                $rawToken->line,
                $rawToken->pos
            );

            $tokenType = match ($rawToken->id) {
                T_LNUMBER => TokenType::INTEGER,
                default => false,
            };
            if (false === $tokenType) {
                $tokenType = match ($rawToken->text) {
                    '+' => TokenType::PLUS,
                    default => false,
                };
                if (false === $tokenType) {
                    continue;
                }
            }
            $this->tokens[] = new Token(
                $tokenType,
                $rawToken->text,
                $rawToken->pos - 6,
                $rawToken->line - 1,
                $rawToken->pos - $linePosLookup[$rawToken->line - 2] - 6
            );
        }

        $this->length = count($this->tokens);
    }

    public function createParseError(): \ParseError
    {
        $current = $this->current();

        return new \ParseError(sprintf('Unexpected token "%s" at line %d, column %d', $current->text, $current->line, $current->column));
    }

    public function current(): Token
    {
        if ($this->isEof()) {
            throw new \RuntimeException('Unexpected end of input');
        }

        return $this->tokens[$this->current];
    }

    public function isEof(): bool
    {
        return $this->current >= $this->length;
    }

    public function matchAny(TokenType ...$types): bool
    {
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
            throw new \RuntimeException('No previous token available');
        }

        return $this->tokens[$this->current - 1];
    }
}
