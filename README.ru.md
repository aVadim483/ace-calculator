# AceCalculator — гибкий универсальный калькулятор на PHP

[![tests](https://github.com/aVadim483/ace-calculator/actions/workflows/tests.yml/badge.svg)](https://github.com/aVadim483/ace-calculator/actions/workflows/tests.yml)

Документация: [English](README.md) | **Русский**

Вы можете вычислять классические математические выражения с переменными,
а можете задать собственные правила вычислений, операторы и функции.

Форк NeonXP/MathExecutor (https://github.com/NeonXP/MathExecutor), значительно доработанный и улучшенный.

Требуется PHP 7.4 или выше, тесты выполняются на PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4 и 8.5.

Содержание:
* [Установка](#установка)
* [Пример использования](#пример-использования)
* [Операторы, функции и константы по умолчанию](#операторы-функции-и-константы-по-умолчанию)
* [Переменные](#переменные)
* [Несколько выражений](#несколько-выражений)
* [Дополнительные операторы и функции](#дополнительные-операторы-и-функции)
* [Пользовательские функции](#пользовательские-функции)
* [Пользовательские операторы](#пользовательские-операторы)
* [Интерпретация идентификаторов](#интерпретация-идентификаторов)
* [Опции](#опции)
* [Нечисловые значения](#нечисловые-значения)
* [Исключения](#исключения)
* [Обработчики ошибок](#обработчики-ошибок)

## Установка

```bash
composer require avadim/ace-calculator
```

Все инструкции по установке здесь: https://packagist.org/packages/avadim/ace-calculator

## Пример использования

```php
require 'vendor/autoload.php';
// создаём калькулятор
$calculator = new \avadim\AceCalculator\AceCalculator();

// вычисляем выражение
print $calculator->execute('1 + 2 * (2 - (4+10))^2 + sin(10)');

// каскадное вычисление — можно вычислить серию выражений,
// переменная $_ содержит результат предыдущего вычисления
print $calculator
        ->calc('4+10')
        ->calc('1 + 2 * (2 - $_)^2') // переменная $_ содержит результат последнего вычисления
        ->calc('$_ + sin(10)')
        ->result();
```

## Операторы, функции и константы по умолчанию

Операторы по умолчанию: `+ - * / ^`

Арифметические функции
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

Тригонометрические функции
* acos()
* acosh()
* asin()
* asinh()
* atan()
* atan2()
* atanh()
* atn() (синоним atan)
* cos()
* cosh()
* deg2rad()
* degrees() (синоним rad2deg)
* rad2deg()
* radians() (синоним deg2rad)
* sin()
* sinh()
* tan()
* tanh()
* tn() (синоним tan)

Константы по умолчанию

PI = 3.14159265358979323846
E = 2.7182818284590452354

Также можно использовать любые стандартные математические константы PHP — M_LOG2E, M_PI_2 и так далее
```php
$calculator->execute('cos(PI)');
$calculator->execute('cos(M_PI)'); // тот же результат
```
## Переменные

Вы можете добавить в калькулятор собственные переменные и использовать их в выражениях

```php
$calculator->setVars([
    'var1' => 0.15,
    'var2' => 0.22
]);

// вычисление с переменными
$calculator->execute('$var1 + $var2');

// вычислить и присвоить результат переменной $var3
$calculator->execute('$var1 + $var2', '$var3');

// присваивание значений переменной прямо в выражении
$calculator
    ->calc('$var3 = ($var1 + $var2)')
    ->calc('$var3 * 20')
    ->result();
```

## Несколько выражений

Несколько выражений можно вычислить за один вызов, разделив их точкой с запятой
```php

$result1 = $calculator
    ->setVar('$var1', 0.15)
    ->setVar('$var2', 0.22)
    ->calc('$var3 = $var1 + $var2')
    ->calc('$var3 * 20')
    ->result()
;
// $result2 будет равен $result1
$result2 = $calculator->execute('$var1=0.15; $var2=0.22; $var3 = $var1 + $var2; $var3 * 20');

```

Точка с запятой внутри строкового литерала разделителем не считается, а пустые части между разделителями просто пропускаются

```php
$calculator->execute('$s = "a;b"; strlength($s)');  // один строковый аргумент, а не два выражения
$calculator->execute('1 + 1;; 2 + 2;');             // вернёт 4
```

Разбиение можно отключить, тогда вся строка вычисляется как одно выражение

```php
$calculator->setMultipleExpressionsEnable(false);
```

## Дополнительные операторы и функции

Расширения с дополнительными операторами и функциями подключаются методом `loadExtension()`:
```php
// загружаем расширение 'Bool'
$calculator->loadExtension('Bool');
```

Это расширение добавляет логические операторы: `< <= > >= == != && ||`

Приоритеты такие же, как в PHP: арифметические операторы связывают сильнее сравнений,
сравнения — сильнее `&&`, а `&&` — сильнее `||`

```php
print $calculator->execute('1 + 1 == 2');            // 1
print $calculator->execute('1 || 0 && 0');           // 1, это 1 || (0 && 0)
```

Логические операторы можно использовать вместе с функциями `if()`, `not()` и `compare()`

```php
print $calculator->execute('if(100+20+3 > 111, 23, 34)');   // 23
print $calculator->execute('not(0)');                       // 1
print $calculator->execute('compare(10, 20)');              // -1 (10 < 20), 0 если равны, 1 если больше
print $calculator->execute('compare(10, 20, "gt")');        // 0, третий аргумент — условие сравнения
```

## Пользовательские функции

Добавляем в калькулятор собственную функцию:
```php
$calculator->addFunction('dummy', function($a) {
    // что-нибудь делаем
    $result = $a * 2;

    return $result;
});

print $calculator->execute('dummy(123)');

// если функция принимает больше одного аргумента, это нужно указать явно

// новая функция hypotenuse() с двумя аргументами
// обратите внимание: "^" — оператор возведения в степень в выражениях, но в коде PHP это "**"
$calculator->addFunction('hypotenuse', function($a, $b) {
    return sqrt($a ** 2 + $b ** 2);
}, 2);

// новая функция nround()
//   1 — минимальное количество аргументов
//   true — используются необязательные аргументы
$calculator->addFunction('nround', function($a, $b = 0) {
    return round($a,  $b);
}, 1, true);

print $calculator->execute('nround(hypotenuse(3,4), 2)');
```

## Пользовательские операторы

Простой способ добавить оператор

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

Альтернативный способ — задать оператор отдельным классом. Создаём класс оператора

```php
<?php
use avadim\AceCalculator\Generic\AbstractToken;
use avadim\AceCalculator\Generic\AbstractTokenOperator;
use avadim\AceCalculator\Token\TokenScalarNumber;

class TokenOperatorModulus extends AbstractTokenOperator
{
    protected static $pattern = 'mod';

    /**
     * Приоритет оператора, чем больше значение, тем выше приоритет
     * (MATH_PRIORITY_PLUS и MATH_PRIORITY_MINUS равны 10, MATH_PRIORITY_MULTIPLY
     * и MATH_PRIORITY_DIVIDE равны 20, MATH_PRIORITY_POWER равен 30,
     * MATH_PRIORITY_UNARY равен 40)
     *
     * @return int
     */
    public function getPriority()
    {
        return self::MATH_PRIORITY_POWER;
    }

    /**
     * Ассоциативность оператора (self::LEFT_ASSOC или self::RIGHT_ASSOC)
     * @return string
     */
    public function getAssociation()
    {
        return self::LEFT_ASSOC;
    }

    /**
     * Выполнение оператора
     * @param AbstractToken[] $stack Стек токенов
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

И передаём класс в калькулятор:

```php
$calculator = new avadim\AceCalculator\AceCalculator();
$calculator->addOperator('mod', \TokenOperatorModulus::class);
echo $calculator->execute('286 mod 100');
```

## Интерпретация идентификаторов

Идентификаторы начинаются с буквы и состоят из букв и цифр. Вы можете задать правила, как интерпретировать их при вычислениях

Значением идентификатора может быть скаляр, функция обратного вызова, выражение с другими идентификаторами или готовый токен

```php
use avadim\AceCalculator\Token\TokenScalarNumber;

$calculator->setIdentifiers([
    'ONE'   => 1,
    'TWO'   => 2,
    'YEAR'  => function($identifier) { return date('Y'); },
    'THREE' => 'ONE + TWO',                 // выражение с другими идентификаторами
    'FIVE'  => new TokenScalarNumber(5),    // готовый токен
]);

$calculator->execute('YEAR + ONE');
```

## Опции

```php
// имя переменной, которая хранит результат последнего вычисления, по умолчанию "_" (то есть $_)
$calculator->setOption('result_variable', 'res');
$calculator->execute('2 + 3');
echo $calculator->getVar('$res');       // 5

// как обрабатывать нечисловые значения в арифметических операциях, см. ниже
$calculator->setOption('non_numeric', true);

echo $calculator->getOption('non_numeric');
```

## Нечисловые значения

Нечисловые значения вызывают предупреждения в арифметических операциях. Однако этого можно избежать, задав специальную опцию.

```php
$calculator = new avadim\AceCalculator\AceCalculator();

// вычисляем выражение с переменной
$calculator->setVar('$x', null);
// на следующей строке будет предупреждение
$calculator->execute('$x * 12');

$calculator->setOption('non_numeric', true);
// а теперь предупреждения не будет
$calculator->execute('$x * 12');
```

## Исключения

Все исключения библиотеки наследуют ```AceCalculatorException``` и хранят код ошибки
в соответствующей константе этого класса

| Исключение | Наследует | Когда возникает |
|---|---|---|
| ```ConfigException``` | ```AceCalculatorException``` | некорректный файл конфигурации или класс токена |
| ```LexerException``` | ```AceCalculatorException``` | неизвестный токен или функция, несогласованные скобки |
| ```ExecException``` | ```AceCalculatorException``` | ошибка самого вычисления |
| ```UnknownVariable``` | ```ExecException``` | неопределённая переменная и обработчик не задан |
| ```UnknownIdentifier``` | ```ExecException``` | неопределённый идентификатор и обработчик не задан |
| ```DivisionByZeroException``` | ```AceCalculatorException``` | деление на ноль и обработчик не задан |

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
    // любая другая ошибка вычисления
}
```

## Обработчики ошибок

### Деление на ноль

Обычно деление на ноль бросает ```DivisionByZeroException```. Но это поведение можно переопределить

```php
$s = '10/0';
$calculator->setDivisionByZeroHandler(static function($a, $b) {
    // $a и $b — первый и второй операнды
    return 0;
});
echo $calculator->execute($s);

```

### Неизвестный идентификатор

Обычно неизвестный идентификатор бросает ```UnknownIdentifier```. Но это поведение можно переопределить

```php
$calculator->setIdentifiers([
    'ONE' => 1,
    'TWO' => 2,
]);

// бросит исключение
echo $calculator->execute('THREE');

$calculator->setUnknownIdentifierHandler(static function($identifier) {
    return $identifier;
});
// вернёт имя идентификатора строкой
echo $calculator->execute('THREE');

$calculator->setUnknownIdentifierHandler(static function($identifier) use ($calculator) {
    return $calculator->execute('ONE + TWO');
});
// вернёт результат выражения ONE + TWO
echo $calculator->execute('THREE');

```

### Неизвестная переменная

Обычно неизвестная переменная бросает ```UnknownVariable```. Но это поведение можно переопределить

```php
$calculator = new avadim\AceCalculator\AceCalculator();

// бросит исключение
$calculator->execute('$a * 4');

// теперь любые неопределённые переменные будут интерпретироваться как 0
// обработчик получает калькулятор, имя неизвестной переменной
// и массив всех определённых переменных
$calculator->setUnknownVariableHandler(static function($calculator, $variable, $variables) {
    return 0;
});
$calculator->execute('$a * 4');
```

## Поддержать AceCalculator

Если пакет оказался полезным, поставьте звезду на Github :)
