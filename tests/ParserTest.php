<?php

declare(strict_types=1);

namespace Manychois\PevalTests;

/**
 * @internal
 *
 * @coversNothing
 */
class ParserTest extends AbstractBaseTestCase
{
    private ExpressionPrinter $printer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->printer = new ExpressionPrinter();
    }

    public function testParseArithmetic1(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('1 + 2 * 3');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "Binary",
                "left": {
                    "type": "Literal",
                    "value": "INTEGER(1) at (1, 1)"
                },
                "operator": "PLUS(+) at (1, 3)",
                "right": {
                    "type": "Binary",
                    "left": {
                        "type": "Literal",
                        "value": "INTEGER(2) at (1, 5)"
                    },
                    "operator": "MULTIPLY(*) at (1, 7)",
                    "right": {
                        "type": "Literal",
                        "value": "INTEGER(3) at (1, 9)"
                    }
                }
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseArithmetic2(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('2 ** 3 ** -4');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "Binary",
                "left": {
                    "type": "Literal",
                    "value": "INTEGER(2) at (1, 1)"
                },
                "operator": "POWER(**) at (1, 3)",
                "right": {
                    "type": "Binary",
                    "left": {
                        "type": "Literal",
                        "value": "INTEGER(3) at (1, 6)"
                    },
                    "operator": "POWER(**) at (1, 8)",
                    "right": {
                        "type": "Unary",
                        "operator": "MINUS(-) at (1, 11)",
                        "operand": {
                            "type": "Literal",
                            "value": "INTEGER(4) at (1, 12)"
                        }
                    }
                }
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseArithmetic3(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('(12 + 34) / (-56 - -78)');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "Binary",
                "left": {
                    "type": "Binary",
                    "left": {
                        "type": "Literal",
                        "value": "INTEGER(12) at (1, 2)"
                    },
                    "operator": "PLUS(+) at (1, 5)",
                    "right": {
                        "type": "Literal",
                        "value": "INTEGER(34) at (1, 7)"
                    }
                },
                "operator": "DIVIDE(/) at (1, 11)",
                "right": {
                    "type": "Binary",
                    "left": {
                        "type": "Unary",
                        "operator": "MINUS(-) at (1, 14)",
                        "operand": {
                            "type": "Literal",
                            "value": "INTEGER(56) at (1, 15)"
                        }
                    },
                    "operator": "MINUS(-) at (1, 18)",
                    "right": {
                        "type": "Unary",
                        "operator": "MINUS(-) at (1, 20)",
                        "operand": {
                            "type": "Literal",
                            "value": "INTEGER(78) at (1, 21)"
                        }
                    }
                }
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseComparison1(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('$a > $b and $c <= $d || $e !== $f && $g === $h');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "Binary",
                "left": {
                    "type": "Binary",
                    "left": {
                        "type": "Variable",
                        "name": "VARIABLE($a) at (1, 1)"
                    },
                    "operator": "GREATER(>) at (1, 4)",
                    "right": {
                        "type": "Variable",
                        "name": "VARIABLE($b) at (1, 6)"
                    }
                },
                "operator": "WORD_AND(and) at (1, 9)",
                "right": {
                    "type": "Binary",
                    "left": {
                        "type": "Binary",
                        "left": {
                            "type": "Variable",
                            "name": "VARIABLE($c) at (1, 13)"
                        },
                        "operator": "LESS_EQUAL(<=) at (1, 16)",
                        "right": {
                            "type": "Variable",
                            "name": "VARIABLE($d) at (1, 19)"
                        }
                    },
                    "operator": "SYMBOL_OR(||) at (1, 22)",
                    "right": {
                        "type": "Binary",
                        "left": {
                            "type": "Binary",
                            "left": {
                                "type": "Variable",
                                "name": "VARIABLE($e) at (1, 25)"
                            },
                            "operator": "NOT_IDENTICAL(!==) at (1, 28)",
                            "right": {
                                "type": "Variable",
                                "name": "VARIABLE($f) at (1, 32)"
                            }
                        },
                        "operator": "SYMBOL_AND(&&) at (1, 35)",
                        "right": {
                            "type": "Binary",
                            "left": {
                                "type": "Variable",
                                "name": "VARIABLE($g) at (1, 38)"
                            },
                            "operator": "IDENTICAL(===) at (1, 41)",
                            "right": {
                                "type": "Variable",
                                "name": "VARIABLE($h) at (1, 45)"
                            }
                        }
                    }
                }
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseComparison2(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('$a or $b xor $c');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "Binary",
                "left": {
                    "type": "Variable",
                    "name": "VARIABLE($a) at (1, 1)"
                },
                "operator": "WORD_OR(or) at (1, 4)",
                "right": {
                    "type": "Binary",
                    "left": {
                        "type": "Variable",
                        "name": "VARIABLE($b) at (1, 7)"
                    },
                    "operator": "XOR(xor) at (1, 10)",
                    "right": {
                        "type": "Variable",
                        "name": "VARIABLE($c) at (1, 14)"
                    }
                }
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseConcat1(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('"Hello " . \'World\' . 123');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "Binary",
                "left": {
                    "type": "Binary",
                    "left": {
                        "type": "Literal",
                        "value": "STRING(\"Hello \") at (1, 1)"
                    },
                    "operator": "DOT(.) at (1, 10)",
                    "right": {
                        "type": "Literal",
                        "value": "STRING('World') at (1, 12)"
                    }
                },
                "operator": "DOT(.) at (1, 20)",
                "right": {
                    "type": "Literal",
                    "value": "INTEGER(123) at (1, 22)"
                }
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseArray1(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('[1, 2, 3,]');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "Array",
                "items": [
                    {
                        "key": null,
                        "value": {
                            "type": "Literal",
                            "value": "INTEGER(1) at (1, 2)"
                        }
                    },
                    {
                        "key": null,
                        "value": {
                            "type": "Literal",
                            "value": "INTEGER(2) at (1, 5)"
                        }
                    },
                    {
                        "key": null,
                        "value": {
                            "type": "Literal",
                            "value": "INTEGER(3) at (1, 8)"
                        }
                    }
                ]
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseArray2(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('array(\'a\', \'b\')');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "Array",
                "items": [
                    {
                        "key": null,
                        "value": {
                            "type": "Literal",
                            "value": "STRING('a') at (1, 7)"
                        }
                    },
                    {
                        "key": null,
                        "value": {
                            "type": "Literal",
                            "value": "STRING('b') at (1, 12)"
                        }
                    }
                ]
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseArray3(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('[\'a\' => 123, "b" => 456]');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "Array",
                "items": [
                    {
                        "key": {
                            "type": "Literal",
                            "value": "STRING('a') at (1, 2)"
                        },
                        "value": {
                            "type": "Literal",
                            "value": "INTEGER(123) at (1, 9)"
                        }
                    },
                    {
                        "key": {
                            "type": "Literal",
                            "value": "STRING(\"b\") at (1, 14)"
                        },
                        "value": {
                            "type": "Literal",
                            "value": "INTEGER(456) at (1, 21)"
                        }
                    }
                ]
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseArray4(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('array(\'a\' => 123, "b" => 456)');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "Array",
                "items": [
                    {
                        "key": {
                            "type": "Literal",
                            "value": "STRING('a') at (1, 7)"
                        },
                        "value": {
                            "type": "Literal",
                            "value": "INTEGER(123) at (1, 14)"
                        }
                    },
                    {
                        "key": {
                            "type": "Literal",
                            "value": "STRING(\"b\") at (1, 19)"
                        },
                        "value": {
                            "type": "Literal",
                            "value": "INTEGER(456) at (1, 26)"
                        }
                    }
                ]
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseArray5(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('$a[1]');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "ArrayAccess",
                "target": {
                    "type": "Variable",
                    "name": "VARIABLE($a) at (1, 1)"
                },
                "offset": {
                    "type": "Literal",
                    "value": "INTEGER(1) at (1, 4)"
                }
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseStringInterpolation(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('"Hello {$name}!"');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "StringInterpolation",
                "parts": [
                    {
                        "type": "Literal",
                        "value": "STRING(Hello ) at (1, 2)"
                    },
                    {
                        "type": "Variable",
                        "name": "VARIABLE($name) at (1, 9)"
                    },
                    {
                        "type": "Literal",
                        "value": "STRING(!) at (1, 15)"
                    }
                ]
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseObjectConstantAccess1(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('MyClass::CONSTANT_A');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "PropertyAccess",
                "target": {
                    "type": "Literal",
                    "value": "IDENTIFIER(MyClass) at (1, 1)"
                },
                "propertyName": {
                    "type": "Literal",
                    "value": "IDENTIFIER(CONSTANT_A) at (1, 10)"
                }
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseObjectConstantAccess2(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('$a::CONSTANT_A');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "PropertyAccess",
                "target": {
                    "type": "Variable",
                    "name": "VARIABLE($a) at (1, 1)"
                },
                "propertyName": {
                    "type": "Literal",
                    "value": "IDENTIFIER(CONSTANT_A) at (1, 5)"
                }
            }
            JSON;
        $this->assertSame($expected, $result);
    }

    public function testParseInstancePropertyAccess(): void
    {
        $parser = new \Manychois\Peval\Parser();
        $expression = $parser->parse('$obj->property');
        $result = $this->printer->print($expression);
        $expected = <<<'JSON'
            {
                "type": "PropertyAccess",
                "target": {
                    "type": "Variable",
                    "name": "VARIABLE($obj) at (1, 1)"
                },
                "propertyName": {
                    "type": "Literal",
                    "value": "IDENTIFIER(property) at (1, 7)"
                }
            }
            JSON;
        $this->assertSame($expected, $result);
    }
}
