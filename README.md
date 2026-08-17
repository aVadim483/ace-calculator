# AceCalculator - flexible universal calculator in PHP

[![tests](https://github.com/aVadim483/ace-calculator/actions/workflows/tests.yml/badge.svg)](https://github.com/aVadim483/ace-calculator/actions/workflows/tests.yml)

You can calculate classical mathematical expressions with variables, 
or you can specify your own calculation rules, operators or custom functions

Forked from NeonXP/MathExecutor (https://github.com/NeonXP/MathExecutor), but advanced and improved.

Requires PHP 7.4 or above, the test suite runs on PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4 and 8.5.

Jump To:
* [Installation](#installation)
* [Sample Usage](#sample-usage)
* [Default operators, functions and constants](#default-operators-functions-and-constants)
* [Variables](#variables)
* [Multiple expressions](#multiple-expressions)
* [Extra operators and functions](#extra-operators-and-functions)
* [Custom functions](#custom-functions)
* [Custom operators](#custom-operators)
* [Interpreting of identifiers](#interpreting-of-identifiers)
* [Options](#options)
* [Non-numeric values](#non-numeric-values)
* [Exceptions](#exceptions)
* [Error Handlers](#error-handlers)

## Installation

```bash
composer require avadim/ace-calculator
```

All instructions to install here: https://packagist.org/packages/avadim/ace-calculator

## Sample Usage

```php
require 'vendor/autoload.php';
// create the calculator
$calculator = new \avadim\AceCalculator\AceCalculator();

// calculate expression
print $calculator->execute('1 + 2 * (2 - (4+10))^2 + sin(10)');

// cascade execution - you can calculate a series of expressions 
// variable $_ has result of previous calculation
print $calculator
        ->calc('4+10')
        ->calc('1 + 2 * (2 - $_)^2') // the variable $_ contains the result of the last calculation
        ->calc('$_ + sin(10)')
        ->result();
```

## Default operators, functions and constants

Default operators: `+ - * / ^`

Arithmetic functions
* abs()
* avg()
* ceil()
* exp()
* expm1()
* floor()
* fmod()
* hypot()
* intdiv()
* log()
* log10()
* log1p()
* max()
* min()
* sqrt()
* round()

Trigonometric functions
* acos()
* acosh()
* asin()
* asinh()
* atan()
* atan2()
* atanh()
* atn() (alias of atan)
* cos()
* cosh()
* deg2rad()
* degrees() (alias of rad2deg)
* rad2deg()
* radians() (alias of deg2rad)
* sin()
* sinh()
* tan()
* tanh()
* tn() (alias of tan)

Default constants

PI = 3.14159265358979323846
E = 2.7182818284590452354

Also you can use any standard math constants from PHP - M_LOG2E, M_PI_2 etc
```php
$calculator->execute('cos(PI)');
$calculator->execute('cos(M_PI)'); // the same result
```
## Variables

You can add own variables to executor and use their in expressions

```php
$calculator->setVars([
    'var1' => 0.15,
    'var2' => 0.22
]);

// calculation with variables
$calculator->execute('$var1 + $var2');

// calculate and assign result to $var3
$calculator->execute('$var1 + $var2', '$var3');

// assign values to variable in expression
$calculator
    ->calc('$var3 = ($var1 + $var2)')
    ->calc('$var3 * 20')
    ->result();
```

## Multiple expressions

You can execute multiple expressions in one by separating them with a semicolon
```php

$result1 = $calculator
    ->setVar('$var1', 0.15)
    ->setVar('$var2', 0.22)
    ->calc('$var3 = $var1 + $var2')
    ->calc('$var3 * 20')
    ->result()
;
// $result2 will be equal $result1
$result2 = $calculator->execute('$var1=0.15; $var2=0.22; $var3 = $var1 + $var2; $var3 * 20');

```

A semicolon inside a string literal is not a separator, and empty parts between separators are just skipped

```php
$calculator->execute('$s = "a;b"; strlength($s)');  // one string argument, not two expressions
$calculator->execute('1 + 1;; 2 + 2;');             // returns 4
```

You can switch the splitting off, then the whole string is calculated as a single expression

```php
$calculator->setMultipleExpressionsEnable(false);
```

## Extra operators and functions

You can load extensions with extra operators and functions by method `loadExtension()`:
```php
// load extension 'Bool'
$calculator->loadExtension('Bool');
```

This extension load boolean operators: `< <= > >= == != && ||`

The priorities are the same as in PHP - the arithmetic operators bind stronger than the comparisons,
the comparisons bind stronger than `&&`, and `&&` binds stronger than `||`

```php
print $calculator->execute('1 + 1 == 2');            // 1
print $calculator->execute('1 || 0 && 0');           // 1, this is 1 || (0 && 0)
```

You can use boolean operators with extra functions `if()`, `not()` and `compare()`

```php
print $calculator->execute('if(100+20+3 > 111, 23, 34)');   // 23
print $calculator->execute('not(0)');                       // 1
print $calculator->execute('compare(10, 20)');              // -1 (10 < 20), 0 if equal, 1 if greater
print $calculator->execute('compare(10, 20, "gt")');        // 0, the third argument is the condition
```

## Custom functions

Add custom function to executor:
```php
$calculator->addFunction('dummy', function($a) {
    // do something
    $result = $a * 2;

    return $result;
});

print $calculator->execute('dummy(123)');

// If the function takes more than 1 argument, you must specify this

// New function hypotenuse() with 2 arguments
// note: "^" is a power operator in expressions, but in PHP code it is "**"
$calculator->addFunction('hypotenuse', function($a, $b) {
    return sqrt($a ** 2 + $b ** 2);
}, 2);

// New function nround()
//   1 - minimum number of arguments
//   true - used optional arguments
$calculator->addFunction('nround', function($a, $b = 0) {
    return round($a,  $b);
}, 1, true);

print $calculator->execute('nround(hypotenuse(3,4), 2)');
```

## Custom operators

A simple way to add an operator

```php
use avadim\AceCalculator\Token\Operator\TokenOperator;
$func = function (array &$stack)
{
    $op2 = array_pop($stack);
    $op1 = array_pop($stack);
    
    return $op1->getValue() % $op2->getValue();
};

$calculator->addOperator('mod', [TokenOperator::MATH_PRIORITY_DIVIDE, $func]);
echo $calculator->execute('286 mod 100');

```

Alternative way to add operator using specified class. Create the class of custom operator

```php
<?php
use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Generic\AbstractTokenOperator;
use avadim\AceCalculator\Token\TokenScalarNumber;

class TokenOperatorModulus extends AbstractTokenOperator
{
    protected static $pattern = 'mod';

    /**
     * Priority of this operator, more value is more priority
     * (MATH_PRIORITY_PLUS and MATH_PRIORITY_MINUS equal 10, MATH_PRIORITY_MULTIPLY
     * and MATH_PRIORITY_DIVIDE equal 20, MATH_PRIORITY_POWER equals 30,
     * MATH_PRIORITY_UNARY equals 40)
     *
     * @return int
     */
    public function getPriority()
    {
        return self::MATH_PRIORITY_POWER;
    }

    /**
     * Association of this operator (self::LEFT_ASSOC or self::RIGHT_ASSOC)
     * @return string
     */
    public function getAssociation()
    {
        return self::LEFT_ASSOC;
    }

    /**
     * Execution of this operator
     * @param AbstractToken[] $stack Stack of tokens
     *
     * @return TokenScalarNumber
     */
    public function execute(array &$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValue() % $op2->getValue();

        return new TokenScalarNumber($result);
    }
}
```

And add the class to executor:

```php
$calculator = new avadim\AceCalculator\AceCalculator();
$calculator->addOperator('mod', \TokenOperatorModulus::class);
echo $calculator->execute('286 mod 100');
```

## Interpreting of identifiers

Identifiers - start with a letter and consist of a sequence of letters and numbers. You can specify rules how to interpret them in calculations

An identifier can be defined as a scalar, a callback, an expression with other identifiers or a ready token

```php
use avadim\AceCalculator\Token\TokenScalarNumber;

$calculator->setIdentifiers([
    'ONE'   => 1,
    'TWO'   => 2,
    'YEAR'  => function($identifier) { return date('Y'); },
    'THREE' => 'ONE + TWO',                 // an expression with other identifiers
    'FIVE'  => new TokenScalarNumber(5),    // a ready token
]);

$calculator->execute('YEAR + ONE');
```

## Options

```php
// the name of the variable which keeps the result of the last calculation, "_" (i.e. $_) by default
$calculator->setOption('result_variable', 'res');
$calculator->execute('2 + 3');
echo $calculator->getVar('$res');       // 5

// how to treat non-numeric values in arithmetic operations, see below
$calculator->setOption('non_numeric', true);

echo $calculator->getOption('non_numeric');
```

## Non-numeric values

Non-numeric values will cause warnings in arithmetic operations. However, you can set a special option to avoid this. 

```php
$calculator = new avadim\AceCalculator\AceCalculator();

// calc expression with variable
$calculator->setVar('$x', null);
// There will be a warning in the next line
$calculator->execute('$x * 12');

$calculator->setOption('non_numeric', true);
// And now there will be no warning
$calculator->execute('$x * 12');
```

## Exceptions

All the exceptions of the library extend ```AceCalculatorException``` and keep the error code
in the corresponding constant of that class

| Exception | Extends | When |
|---|---|---|
| ```ConfigException``` | ```AceCalculatorException``` | a bad config file or a bad token class |
| ```LexerException``` | ```AceCalculatorException``` | an unknown token or function, incorrect brackets |
| ```ExecException``` | ```AceCalculatorException``` | an error of the calculation itself |
| ```UnknownVariable``` | ```ExecException``` | an undefined variable and no handler is set |
| ```UnknownIdentifier``` | ```ExecException``` | an undefined identifier and no handler is set |
| ```DivisionByZeroException``` | ```AceCalculatorException``` | division by zero and no handler is set |

```php
use avadim\AceCalculator\Exception\ExecException;
use avadim\AceCalculator\Exception\UnknownVariable;

try {
    $calculator->execute('$a * 4');
} catch (UnknownVariable $e) {
    echo $e->getCode();                 // ExecException::CALC_UNKNOWN_VARIABLE
    echo $e->getErrorMessage();         // Unknown variable "$a"
    echo $e->getErrorExpression();      // $a * 4
} catch (ExecException $e) {
    // any other error of the calculation
}
```

## Error Handlers

### Division by zero

Usually division by zero throws a ```DivisionByZeroException```. But you can redefine this behavior

```php
$s = '10/0';
$calculator->setDivisionByZeroHandler(static function($a, $b) {
    // $a and $b - the first and second operands
    return 0;
});
echo $calculator->execute($s);

```

### Unknown Identifier

Usually unknown identifier throws a ```UnknownIdentifier```. But you can redefine this behavior

```php
$calculator->setIdentifiers([
    'ONE' => 1,
    'TWO' => 2,
]);

// Will throw an exception
echo $calculator->execute('THREE');

$calculator->setUnknownIdentifierHandler(static function($identifier) {
    return $identifier;
});
// Returns name of identifier as string 
echo $calculator->execute('THREE');

$calculator->setUnknownIdentifierHandler(static function($identifier) use ($calculator) {
    return $calculator->execute('ONE + TWO');
});
// Returns result of expression ONE + TWO
echo $calculator->execute('THREE');

```

### Unknown Variable

Usually unknown variable throws a ```UnknownVariable```. But you can redefine this behavior

```php
$calculator = new avadim\AceCalculator\AceCalculator();

// Will throw an exception
$calculator->execute('$a * 4');

// Now any undefined variables will be interpreted as 0
// The handler takes the calculator, the name of the unknown variable
// and the array of all defined variables
$calculator->setUnknownVariableHandler(static function($calculator, $variable, $variables) {
    return 0;
});
$calculator->execute('$a * 4');
```

## Support AceCalculator

if you find this package useful you just give me star on Github :)