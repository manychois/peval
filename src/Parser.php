<?php

declare(strict_types=1);

namespace Manychois\Peval;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use RuntimeException;
use WeakReference;

/**
 * Parses PHP expression into a node tree.
 */
final class Parser
{
    /**
     * @var WeakReference<PhpParser>|null
     */
    private static ?WeakReference $phpParserRef = null;

    private static function getPhpParser(): PhpParser
    {
        $phpParser = self::$phpParserRef?->get();
        if (!($phpParser instanceof PhpParser)) {
            $phpParser = (new ParserFactory())->createForNewestSupportedVersion();
            self::$phpParserRef = WeakReference::create($phpParser);
        }

        return $phpParser;
    }

    /**
     * Parses a PHP expression from a string and returns the corresponding AST node.
     *
     * @param string $source the PHP expression to parse
     *
     * @return Expr the parsed expression node
     */
    public function parse(string $source): Expr
    {
        $ast = $this->toAst($source);
        $stmt = $ast[0] ?? null;
        if (1 !== count($ast) || !($stmt instanceof Stmt\Expression)) {
            throw new RuntimeException('Invalid expression: ' . $source);
        }

        $assignExpr = $stmt->expr;
        if (!($assignExpr instanceof Expr\Assign)) {
            throw new RuntimeException('Invalid expression: ' . $source);
        }

        return $assignExpr->expr;
    }

    /**
     * @return array<Stmt>
     */
    private function toAst(string $source): array
    {
        $randomVariable = 'peval_' . bin2hex(random_bytes(8));
        $validSource = sprintf('<?php $%s = (%s);', $randomVariable, $source);

        return self::getPhpParser()->parse($validSource) ?? [];
    }
}
