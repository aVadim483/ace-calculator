# AceCalculator - flexible universal calculator

Based on NeonXP/MathExecutor (https://github.com/NeonXP/MathExecutor)

You can calculate classical mathematical expressions with variables, 
or you can specify your own calculation rules, operators or custom functions

## Install via Composer

|$ composer require avadim/ace-claculator

All instructions to install here: https://packagist.org/packages/avadim/ace-claculator

## Sample usage

```php
require 'vendor/autoload.php';
// create the calculator
$calculator = new \avadim\AceClaculator\AceClaculator();

// calculate expression
print $calculator->execute('1 + 2 * (2 - (4+10))^2 + sin(10)');

// cascade execution - you can calculate a series of expressions 
// variable $_ has result of previous calculation
print $calculator
        ->calc('4+10')
        ->calc('1 + 2 * (2 - $_)^2') // the variable $_ contains the result of the last calculation
        ->calc('$_ + sin(10)')
        ->getResult();
```

## Default operators and functions

Default operators: `+ - * / ^`

Default functions:
* min
* max
* avg
* sqrt
* sin
* cos
* tn
* asin
* acos
* atn

## Variables

Default variables:

```
$pi = 3.14159265359
$e = 2.71828182846
```

You can add own variable to executor:

```php
$calculator->setVars([
    'var1' => 0.15,
    'var2' => 0.22
]);

$calculator->execute('$var1 + $var2');
```

## Extra operators and functions

You can load extensions with extra operators and functions by method `loadExtension()`:
```php
// load extension 'Bool'
$calculator->loadExtension('Bool');
```

This extension load boolean operators: `< <= > >= == !=`

You can use boolean operators with extra function `if()`

```php
print $calculator->execute('if(100+20+3 > 111, 23, 34)');
```

## Custom functions

Add custom function to executor:
```php
$calculator->addFunction('hypotenuse', function($a, $b) {
    return sqrt($a^2 + $b^2);
}, 2);

print $calculator->execute('hypotenuse(3,4)');
```

## Custom operators

Create the class of custom operator:

```php
<?php
use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Generic\AbstractTokenOperator;
use avadim\AceCalculator\Token\TokenScalarNumber;

class TokenOperatorModulus extends AbstractTokenOperator
{
    protected static $pattern = 'mod';

    /**
     * Priority of this operator (1 equals "+" or "-", 2 equals "*" or "/", 3 equals "^")
     * @return int
     */
    public function getPriority()
    {
        return 3;
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
    public function execute(&$stack)
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
$calculator = new avadim\AceClaculator\AceClaculator();
$calculator->addOperator('mod', '\TokenOperatorModulus');
echo $calculator->execute('286 mod 100');
```

## Interpreting of identifiers

Identifiers - start with a letter and consist of a sequence of letters and numbers. You can specify rules how to interpret them in calculations

```php
$calculator->setIdentifiers([
    'ONE' => 1,
    'YEAR' => function($variables, $identifiers) { return date('Y'); },
]);

$calculator->execute('YEAR + ONE');
```
