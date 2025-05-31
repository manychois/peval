<?php

declare(strict_types=1);

namespace Manychois\Peval\Tokenisation;

class Token
{
    public readonly TokenType $type;
    public readonly string $text;
    public readonly int $position;
    public readonly int $line;
    public readonly int $column;

    public function __construct(TokenType $type, string $text, int $position, int $line, int $column)
    {
        $this->type = $type;
        $this->text = $text;
        $this->position = $position;
        $this->line = $line;
        $this->column = $column;
    }
}
