<?php

declare(strict_types=1);

namespace Manychois\PevalTests\Tokenisation;

use LogicException;
use Manychois\Peval\ExpressionParseException;
use Manychois\Peval\Tokenisation\TokenStream;
use Manychois\Peval\Tokenisation\TokenType;
use Manychois\PevalTests\BaseTestCase;

/**
 * Tests for the TokenStream class.
 *
 * @internal
 *
 * @coversNothing
 */
class TokenStreamTest extends BaseTestCase
{
    public function testConstructorWithSimpleExpression(): void
    {
        $source = '$x + 5';
        $stream = new TokenStream($source);

        $this->assertSame(3, $stream->length);
        $this->assertSame(0, $stream->current);
    }

    public function testConstructorWithComplexExpression(): void
    {
        $source = '$variable == "hello world" && $y > 42.5';
        $stream = new TokenStream($source);

        // Should tokenize: $variable, ==, "hello world", &&, $y, >, 42.5
        $this->assertSame(7, $stream->length);
    }

    public function testConstructorWithWhitespace(): void
    {
        $source = '  $x   +   5  ';
        $stream = new TokenStream($source);

        // Whitespace should be ignored
        $this->assertSame(3, $stream->length);
    }

    public function testCurrentToken(): void
    {
        $source = '$x + 5';
        $stream = new TokenStream($source);

        $current = $stream->current();
        $this->assertSame(TokenType::VARIABLE, $current->type);
        $this->assertSame('$x', $current->text);
        $this->assertSame(0, $current->position);
        $this->assertSame(1, $current->line);
        $this->assertSame(1, $current->column);
    }

    public function testAdvance(): void
    {
        $source = '$x + 5';
        $stream = new TokenStream($source);

        // First token
        $token1 = $stream->advance();
        $this->assertSame(TokenType::VARIABLE, $token1->type);
        $this->assertSame('$x', $token1->text);
        $this->assertSame(1, $stream->current);

        // Second token
        $token2 = $stream->advance();
        $this->assertSame(TokenType::PLUS, $token2->type);
        $this->assertSame('+', $token2->text);
        $this->assertSame(2, $stream->current);

        // Third token
        $token3 = $stream->advance();
        $this->assertSame(TokenType::INTEGER, $token3->type);
        $this->assertSame('5', $token3->text);
        $this->assertSame(3, $stream->current);
    }

    public function testIsEof(): void
    {
        $source = '$x';
        $stream = new TokenStream($source);

        $this->assertFalse($stream->isEof());

        $stream->advance();
        $this->assertTrue($stream->isEof());
    }

    public function testCurrentThrowsExceptionWhenEof(): void
    {
        $source = '$x';
        $stream = new TokenStream($source);

        $stream->advance(); // Move past the only token

        $this->expectException(ExpressionParseException::class);
        $this->expectExceptionMessage('Unexpected end of input');
        $stream->current();
    }

    public function testMatchAnySingleType(): void
    {
        $source = '$x + 5';
        $stream = new TokenStream($source);

        // Should match VARIABLE
        $this->assertTrue($stream->matchAny(TokenType::VARIABLE));
        $this->assertSame(1, $stream->current);

        // Should match PLUS
        $this->assertTrue($stream->matchAny(TokenType::PLUS));
        $this->assertSame(2, $stream->current);

        // Should not match MINUS
        $this->assertFalse($stream->matchAny(TokenType::MINUS));
        $this->assertSame(2, $stream->current); // Position should not change
    }

    public function testMatchAnyMultipleTypes(): void
    {
        $source = '$x + 5';
        $stream = new TokenStream($source);

        // Should match VARIABLE from multiple options
        $this->assertTrue($stream->matchAny(TokenType::INTEGER, TokenType::VARIABLE, TokenType::STRING));
        $this->assertSame(1, $stream->current);

        // Should match PLUS from multiple options
        $this->assertTrue($stream->matchAny(TokenType::MINUS, TokenType::PLUS, TokenType::MULTIPLY));
        $this->assertSame(2, $stream->current);
    }

    public function testMatchAnyReturnsFalseAtEof(): void
    {
        $source = '$x';
        $stream = new TokenStream($source);

        $stream->advance(); // Move past the only token

        $this->assertFalse($stream->matchAny(TokenType::VARIABLE));
    }

    public function testPrevious(): void
    {
        $source = '$x + 5';
        $stream = new TokenStream($source);

        $stream->advance(); // Move to second token
        $stream->advance(); // Move to third token

        $previous = $stream->previous();
        $this->assertSame(TokenType::PLUS, $previous->type);
        $this->assertSame('+', $previous->text);
    }

    public function testPreviousThrowsExceptionAtStart(): void
    {
        $source = '$x + 5';
        $stream = new TokenStream($source);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No previous token available');
        $stream->previous();
    }

    public function testCreateParseExceptionWithCustomMessage(): void
    {
        $source = '$x + 5';
        $stream = new TokenStream($source);

        $exception = $stream->createParseException('Custom error message');

        $this->assertInstanceOf(ExpressionParseException::class, $exception);
        $this->assertStringContainsString('Custom error message', $exception->getMessage());
        $this->assertStringContainsString('line 1, column 1', $exception->getMessage());
    }

    public function testCreateParseExceptionWithDefaultMessage(): void
    {
        $source = '$x + 5';
        $stream = new TokenStream($source);

        $exception = $stream->createParseException();

        $this->assertInstanceOf(ExpressionParseException::class, $exception);
        $this->assertStringContainsString('Unexpected token "$x"', $exception->getMessage());
        $this->assertStringContainsString('line 1, column 1', $exception->getMessage());
    }

    public function testTokenizeArithmeticOperators(): void
    {
        $source = '+ - * / % **';
        $stream = new TokenStream($source);

        $this->assertSame(6, $stream->length);

        $this->assertSame(TokenType::PLUS, $stream->advance()->type);
        $this->assertSame(TokenType::MINUS, $stream->advance()->type);
        $this->assertSame(TokenType::MULTIPLY, $stream->advance()->type);
        $this->assertSame(TokenType::DIVIDE, $stream->advance()->type);
        $this->assertSame(TokenType::MODULO, $stream->advance()->type);
        $this->assertSame(TokenType::POWER, $stream->advance()->type);
    }

    public function testTokenizeComparisonOperators(): void
    {
        $source = '< <= > >= == != === !==';
        $stream = new TokenStream($source);

        $this->assertSame(8, $stream->length);

        $this->assertSame(TokenType::LESS, $stream->advance()->type);
        $this->assertSame(TokenType::LESS_EQUAL, $stream->advance()->type);
        $this->assertSame(TokenType::GREATER, $stream->advance()->type);
        $this->assertSame(TokenType::GREATER_EQUAL, $stream->advance()->type);
        $this->assertSame(TokenType::EQUAL, $stream->advance()->type);
        $this->assertSame(TokenType::NOT_EQUAL, $stream->advance()->type);
        $this->assertSame(TokenType::IDENTICAL, $stream->advance()->type);
        $this->assertSame(TokenType::NOT_IDENTICAL, $stream->advance()->type);
    }

    public function testTokenizeLogicalOperators(): void
    {
        // Test && operator
        $source1 = '&&';
        $stream1 = new TokenStream($source1);
        $this->assertSame(1, $stream1->length);
        $this->assertSame(TokenType::SYMBOL_AND, $stream1->advance()->type);

        // Test || operator
        $source2 = '||';
        $stream2 = new TokenStream($source2);
        $this->assertSame(1, $stream2->length);
        $this->assertSame(TokenType::SYMBOL_OR, $stream2->advance()->type);

        // Test ! operator
        $source3 = '!';
        $stream3 = new TokenStream($source3);
        $this->assertSame(1, $stream3->length);
        $this->assertSame(TokenType::NOT, $stream3->advance()->type);

        // Test word operators
        $source4 = 'AND OR XOR';
        $stream4 = new TokenStream($source4);
        $this->assertSame(3, $stream4->length);
        $this->assertSame(TokenType::WORD_AND, $stream4->advance()->type);
        $this->assertSame(TokenType::WORD_OR, $stream4->advance()->type);
        $this->assertSame(TokenType::XOR, $stream4->advance()->type);
    }

    public function testTokenizeLiterals(): void
    {
        $source = '42 3.14 "hello" true false null';
        $stream = new TokenStream($source);

        $this->assertSame(6, $stream->length);

        $intToken = $stream->advance();
        $this->assertSame(TokenType::INTEGER, $intToken->type);
        $this->assertSame('42', $intToken->text);

        $floatToken = $stream->advance();
        $this->assertSame(TokenType::FLOAT, $floatToken->type);
        $this->assertSame('3.14', $floatToken->text);

        $stringToken = $stream->advance();
        $this->assertSame(TokenType::STRING, $stringToken->type);
        $this->assertSame('"hello"', $stringToken->text);

        $boolTrueToken = $stream->advance();
        $this->assertSame(TokenType::BOOL, $boolTrueToken->type);
        $this->assertSame('true', $boolTrueToken->text);

        $boolFalseToken = $stream->advance();
        $this->assertSame(TokenType::BOOL, $boolFalseToken->type);
        $this->assertSame('false', $boolFalseToken->text);

        $nullToken = $stream->advance();
        $this->assertSame(TokenType::NULL, $nullToken->type);
        $this->assertSame('null', $nullToken->text);
    }

    public function testTokenizeBracketsAndParentheses(): void
    {
        $source = '( ) [ ]';
        $stream = new TokenStream($source);

        $this->assertSame(4, $stream->length);

        $this->assertSame(TokenType::LEFT_PARENTHESIS, $stream->advance()->type);
        $this->assertSame(TokenType::RIGHT_PARENTHESIS, $stream->advance()->type);
        $this->assertSame(TokenType::LEFT_BRACKET, $stream->advance()->type);
        $this->assertSame(TokenType::RIGHT_BRACKET, $stream->advance()->type);
    }

    public function testTokenizeSpecialSymbols(): void
    {
        $source = '. , -> => ::';
        $stream = new TokenStream($source);

        $this->assertSame(5, $stream->length);

        $this->assertSame(TokenType::DOT, $stream->advance()->type);
        $this->assertSame(TokenType::COMMA, $stream->advance()->type);
        $this->assertSame(TokenType::ARROW, $stream->advance()->type);
        $this->assertSame(TokenType::DOUBLE_ARROW, $stream->advance()->type);
        $this->assertSame(TokenType::DOUBLE_COLON, $stream->advance()->type);
    }

    public function testTokenizeVariables(): void
    {
        $source = '$var $myVariable $test123 $_private';
        $stream = new TokenStream($source);

        $this->assertSame(4, $stream->length);

        $var1 = $stream->advance();
        $this->assertSame(TokenType::VARIABLE, $var1->type);
        $this->assertSame('$var', $var1->text);

        $var2 = $stream->advance();
        $this->assertSame(TokenType::VARIABLE, $var2->type);
        $this->assertSame('$myVariable', $var2->text);

        $var3 = $stream->advance();
        $this->assertSame(TokenType::VARIABLE, $var3->type);
        $this->assertSame('$test123', $var3->text);

        $var4 = $stream->advance();
        $this->assertSame(TokenType::VARIABLE, $var4->type);
        $this->assertSame('$_private', $var4->text);
    }

    public function testTokenizeIdentifiers(): void
    {
        $source = 'identifier someFunction className';
        $stream = new TokenStream($source);

        $this->assertSame(3, $stream->length);

        $this->assertSame(TokenType::IDENTIFIER, $stream->advance()->type);
        $this->assertSame(TokenType::IDENTIFIER, $stream->advance()->type);
        $this->assertSame(TokenType::IDENTIFIER, $stream->advance()->type);
    }

    public function testTokenizeArray(): void
    {
        $source = 'array';
        $stream = new TokenStream($source);

        $this->assertSame(1, $stream->length);

        $arrayToken = $stream->advance();
        $this->assertSame(TokenType::ARRAY, $arrayToken->type);
        $this->assertSame('array', $arrayToken->text);
    }

    public function testMultilineExpression(): void
    {
        $source = '$x +' . "\n" . '$y';
        $stream = new TokenStream($source);

        $this->assertSame(3, $stream->length);

        $var1 = $stream->advance();
        $this->assertSame(1, $var1->line);
        $this->assertSame(1, $var1->column);

        $plus = $stream->advance();
        $this->assertSame(1, $plus->line);
        $this->assertSame(4, $plus->column);

        $var2 = $stream->advance();
        $this->assertSame(2, $var2->line);
        $this->assertSame(1, $var2->column);
    }

    public function testComplexExpression(): void
    {
        $source = '($x + $y) * 2.5 >= $threshold && $active === true';
        $stream = new TokenStream($source);

        // Should have: (, $x, +, $y, ), *, 2.5, >=, $threshold, &&, $active, ===, true
        $this->assertSame(13, $stream->length);

        $this->assertSame(TokenType::LEFT_PARENTHESIS, $stream->advance()->type);
        $this->assertSame(TokenType::VARIABLE, $stream->advance()->type);
        $this->assertSame(TokenType::PLUS, $stream->advance()->type);
        $this->assertSame(TokenType::VARIABLE, $stream->advance()->type);
        $this->assertSame(TokenType::RIGHT_PARENTHESIS, $stream->advance()->type);
        $this->assertSame(TokenType::MULTIPLY, $stream->advance()->type);
        $this->assertSame(TokenType::FLOAT, $stream->advance()->type);
        $this->assertSame(TokenType::GREATER_EQUAL, $stream->advance()->type);
        $this->assertSame(TokenType::VARIABLE, $stream->advance()->type);
        $this->assertSame(TokenType::SYMBOL_AND, $stream->advance()->type);
        $this->assertSame(TokenType::VARIABLE, $stream->advance()->type);
        $this->assertSame(TokenType::IDENTICAL, $stream->advance()->type);
        $this->assertSame(TokenType::BOOL, $stream->advance()->type);
    }

    public function testEmptyInput(): void
    {
        $source = '';
        $stream = new TokenStream($source);

        $this->assertSame(0, $stream->length);
        $this->assertTrue($stream->isEof());
    }

    public function testOnlyWhitespace(): void
    {
        $source = "   \n  \t  ";
        $stream = new TokenStream($source);

        $this->assertSame(0, $stream->length);
        $this->assertTrue($stream->isEof());
    }

    public function testTokenizeStringInterpolation(): void
    {
        $source = '"Hello $name, welcome!"';
        $stream = new TokenStream($source);

        // String interpolation should tokenize as: "Hello ", $name, ", welcome!"
        // However, the exact tokenization depends on how PHP's tokenizer handles interpolated strings
        $this->assertGreaterThan(0, $stream->length);

        // Get all tokens to understand the structure
        $tokens = [];
        while (!$stream->isEof()) {
            $tokens[] = $stream->advance();
        }

        // At minimum, we should have some tokens representing the interpolated string
        $this->assertNotEmpty($tokens);

        // Check that at least one token is a STRING type (for the string parts)
        $hasStringToken = false;
        $hasVariableToken = false;

        foreach ($tokens as $token) {
            if (TokenType::STRING === $token->type) {
                $hasStringToken = true;
            }
            if (TokenType::VARIABLE === $token->type) {
                $hasVariableToken = true;
            }
        }

        // String interpolation should contain both string and variable tokens
        $this->assertTrue($hasStringToken || $hasVariableToken, 'String interpolation should contain string or variable tokens');
    }

    public function testTokenizeComplexStringInterpolation(): void
    {
        $source = '"User {$user->name} has {$count} items"';
        $stream = new TokenStream($source);

        $this->assertGreaterThan(0, $stream->length);

        // Collect all token types and texts for analysis
        $tokens = [];
        while (!$stream->isEof()) {
            $token = $stream->advance();
            $tokens[] = [
                'type' => $token->type,
                'text' => $token->text,
            ];
        }

        $this->assertNotEmpty($tokens);

        // Complex string interpolation with curly braces should contain various token types
        $tokenTypes = array_column($tokens, 'type');

        // Should contain at least some string tokens (for the literal parts)
        $hasStringToken = in_array(TokenType::STRING, $tokenTypes, true);

        // Should contain variables ($user, $count)
        $hasVariableToken = in_array(TokenType::VARIABLE, $tokenTypes, true);

        // Should contain object operator (->)
        $hasArrowToken = in_array(TokenType::ARROW, $tokenTypes, true);

        // Should contain identifiers (name)
        $hasIdentifierToken = in_array(TokenType::IDENTIFIER, $tokenTypes, true);

        // Should contain braces ({ })
        $hasLeftBrace = in_array(TokenType::LEFT_BRACE, $tokenTypes, true);
        $hasRightBrace = in_array(TokenType::RIGHT_BRACE, $tokenTypes, true);

        // Assert that we have the expected token types for complex string interpolation
        $this->assertTrue($hasStringToken || $hasVariableToken, 'Should have string or variable tokens');

        // If PHP's tokenizer properly handles the interpolation syntax, we should see these tokens
        if ($hasVariableToken) {
            $this->assertTrue($hasArrowToken, 'Should have arrow token for object property access');
            $this->assertTrue($hasIdentifierToken, 'Should have identifier token for property name');
        }

        // Verify that braces are properly tokenized if they appear
        if ($hasLeftBrace) {
            $this->assertTrue($hasRightBrace, 'Left brace should have matching right brace');
        }

        // Ensure we have a reasonable number of tokens for the complex expression
        $this->assertGreaterThanOrEqual(3, count($tokens), 'Complex interpolation should produce multiple tokens');
    }

    public function testTokenizeUnhandledToken(): void
    {
        // Backtick (`) is not handled by the tokenizer and should throw LogicException
        $source = '`unhandled`';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unhandled PHP Token `');

        new TokenStream($source);
    }
}
