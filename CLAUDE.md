# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

`avadim/ace-calculator` — a PHP library (PHP >= 7.4, PSR-4 `avadim\AceCalculator\` → `src/`) that evaluates
arbitrary expressions with pluggable operators, functions, variables and identifiers.
Fork of NeonXP/MathExecutor, heavily reworked.

## Commands

```bash
composer update             # no composer.lock is committed; "bin-dir": "bin", so binaries land in ./bin, NOT ./vendor/bin
bin/phpunit                 # run all tests (config: phpunit.xml.dist, bootstrap: tests/bootstrap.php)
bin/phpunit --filter testBool                       # single test method
bin/phpunit tests/ExtensionTest.php                 # single file
bin/phpunit --testdox                               # readable list of what is covered
```

Demos are browser-based: point a vhost/`php -S` at `demo/` and open `demo/index.php`.
They bootstrap through `src/autoload.php` (a standalone SPL autoloader), so they run without composer.

CI (`.github/workflows/tests.yml`) runs the suite on PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4 and 8.5.
Because there is no lock file, each job installs the newest PHPUnit its PHP version allows
(9.6 on PHP 7.4/8.0, 10.5 on 8.1, 11.5 on 8.2, 12.x on 8.3+), so **everything in `src/` and `tests/`
must stay parsable and green on PHP 7.4** — no union types, promoted properties, `match`, enums or
named arguments, and no implicit nullable parameters (`?string $x = null`, never `string $x = null`,
which is deprecated since 8.4).

## Architecture

Evaluation pipeline (`AceCalculator::calc()` → `calcExpression()`):

1. **`Lexer::parse()`** splits the input into lexemes by handing it to PHP's own tokenizer
   (`token_get_all('<?php ' . $input)`). Consequence: lexeme boundaries follow PHP's rules. Because PHP
   would swallow `#…` and `//…` as comments, any lexeme starting with `#` or `/` is split off and its
   remainder re-parsed recursively.
2. **`TokenFactory::createToken()`** turns each lexeme into a token object. `$lexemeNum` is passed **by
   reference**, so a token may consume several lexemes (see multi-lexeme matching below).
3. **`Lexer::getTokensStream()`** post-processes the stream: an identifier immediately followed by
   `TokenLeftBracket` is replaced by a `TokenFunction`.
4. **`Lexer::buildReversePolishNotation()`** — shunting-yard. Left brackets of *function* calls are pushed to
   the output too; they act as the argument-list terminator that `TokenFunction::execute()` pops against.
   `TokenOperatorAssign` is special-cased: the preceding token must be a `TokenVariable` at the start of an
   expression (`getOption('begin')`), and it is flagged `assignVariable = true`.
5. **`Processor::calculate()`** walks the RPN as a stack machine, resolving variables/identifiers and calling
   `execute()` on functions and operators. `Processor` keeps an execution log (`getLog(true)` renders it).

`Container` is a tiny service registry holding `Calculator`, `TokenFactory`, `Lexer`, `Processor`. It is
injected into every token, which is how tokens reach back into the calculator — e.g. `TokenOperatorAssign`
calls `Calculator::setVar()`, `TokenOperatorDivide` fetches the division-by-zero handler.
`AceCalculator::init()` rebuilds the whole container; `__clone()` re-runs it so clones don't share state.
`createTokenFactory()`/`createLexer()`/`createProcessor()` are the extension points for subclasses.

### Config-driven registration

Everything is a config array (`AceCalculator::getDefaults()`): `options`, `tokens`, `operators`, `functions`,
`variables`, `identifiers`. `applyConfig()` feeds them into the `TokenFactory`. All PHP `M_*` constants are
auto-registered as identifiers.

Functions are stored as the 4-tuple `[$name, $minArguments, $callback, $variableArguments]` built by
`AceCalculator::createFunction()`. In config a function value is `callable` or
`[callable, minArguments, variableArguments]`; `minArguments === -1` means "0 or more, variadic".

### Token matching (order matters)

Each token class exposes static `getMatching($pattern)` returning `pattern` / `matching` / `callback` /
`lexemes_max`. Matching modes on `AbstractToken`: `MATCH_STRING`, `MATCH_NUMERIC`, `MATCH_REGEX`,
`MATCH_CALLBACK`. `TokenFactory::createToken()` iterates `$this->tokens` **in insertion order** and returns
the first match — so `addOperator()` *prepends* (operators must be tried before the generic identifier/number
tokens) while `addToken()` appends.

Multi-lexeme tokens have two routes: set `$lexemes_max > 1` (the factory concatenates lexemes and retries), or
use `MATCH_CALLBACK` with an `isMatch()` that advances `$lexemeNum` by reference and returns the joined string
— see `Extension\Colors\TokenScalarHexString` assembling `#ff00aa`.

### Variables vs identifiers

- **Variables** carry the `var_prefix` (`$`, configurable) and live in `AceCalculator::$variables`.
  `setVar()`/`getVar()` add the prefix automatically. The `result_variable` (`_`, i.e. `$_`) always holds the
  previous result, which is what makes `->calc()->calc()->result()` chaining work.
- **Identifiers** are bare words. A value may be a scalar, a `callable`, or an **expression string** — the
  latter two are resolved recursively through `Calculator::execute()`, so identifiers can reference other
  identifiers (`'THREE' => 'ONE + TWO'`).

`execute()` = `calc()` + `result()`. `calc()` splits on `;` when `multipleExpressions` is on (default).
Parsed RPN is cached per expression string in `AceCalculator::$cache` (toggle with `cacheEnable()`).

### Extensions

`loadExtension('Bool')` → `loadConfig(src/Extension/Bool/config.php)`. An extension config may declare:
`extensions` (load other extensions first — `ColorsHexa` does this to override `Colors`), `include` (glob
patterns `include_once`'d relative to the config file — needed because extensions ship **namespaced plain
functions** in `functions.php`, which no autoloader can find), plus the usual `tokens`/`operators`/`functions`.
Adding a new extension means creating `src/Extension/<Name>/config.php` in that shape.

### Errors and lenient modes

All exceptions extend `AceCalculatorException` (abstract, `RuntimeException`) and share its numeric code
constants. Several failure modes are overridable instead of fatal:
`setDivisionByZeroHandler()`, `setUnknownIdentifierHandler()`, `setUnknownVariableHandler()` (the latter two
delegate to `Processor::setHandler()`), and the `non_numeric` option, which switches
`AbstractToken::getValueNum()` between "emit an `E_USER_WARNING`" and "silently cast".

## Conventions

- Every `src/` file carries the package header comment block; keep it on new files.
- Fluent setters return `$this` throughout the public API.
- New operators extend `AbstractTokenOperator` (implement `getPriority()`, `getAssociation()`, `execute(&$stack)`)
  and use the `MATH_PRIORITY_*` constants for priority. `Token\Operator\TokenOperator` is the generic
  closure-backed operator used by `addOperator($name, [$priority, $callback])`.
- Operators `execute(&$stack)` pop operands in reverse (`$op2` then `$op1`) and return a token, usually
  `TokenScalarNumber`.
- Tests live in `avadim\AceCalculator\Test\` (`autoload-dev` → `tests/`), use `declare(strict_types=1)` and
  are split by concern: `AceCalculatorTest` (arithmetic, default functions, errors), `VariablesTest`,
  `CustomizationTest` (custom functions/operators, options, handlers), `ExtensionTest`. Helper classes go to
  `tests/Fixture/` (not picked up by the runner — only `*Test.php` is).
- Data providers must be **static** and carry **both** `@dataProvider` and `#[DataProvider]`: PHPUnit 9 reads
  only the annotation, PHPUnit 12 reads only the attribute, and on PHP 7.4 the attribute line is just a
  comment. Dropping either one breaks part of the matrix.
- `testCalculating` cross-checks results against PHP `eval()` (`^` is rewritten to `**`); expressions whose
  semantics differ from PHP on purpose (e.g. `-2 ^ 2` is `4` here, because unary operators outrank `^`)
  must be asserted explicitly instead of going through that provider.
