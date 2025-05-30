<?php

declare(strict_types=1);

namespace Manychois\Peval;

use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\LiteralExpression;

class Parser
{
    public function parse(string $source): ExpressionInterface
    {
        $lp = new LexerProgress($source);

        return $this->parseExpression($lp);
    }

    private function parseExpression(LexerProgress $lp): ExpressionInterface
    {
        return $this->parsePrimary($lp);
    }

    private function parsePrimary(LexerProgress $lp): ExpressionInterface
    {
        if ($lp->matchAny(TokenType::INTEGER)) {
            return new LiteralExpression($lp->previous());
        }

        throw $lp->createParseError();
    }
}
