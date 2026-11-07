# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

TwigStan is a static analyzer for Twig templates: it compiles templates to PHP and has PHPStan analyze them, then maps errors back to the original template and line. Requires PHP ^8.3. See README.md for the user-facing description.

## Commands

Tasks are defined in `castor.php` (Castor task runner). A Docker setup is provided so nothing has to be installed on the host:

```
docker compose up -d                       # dev container, code mounted at /app
docker compose exec php composer install
docker compose exec php castor <task>      # any task below
docker compose run --rm tests              # one-off full test run
PHP_VERSION=8.4 docker compose up -d --build   # other PHP version (CI runs 8.3, 8.4, 8.5)
```

Inside the container (or on a host with Castor):

- `castor phpunit` (aliases `test`, `tests`): extra args go straight to PHPUnit, e.g. `castor phpunit --filter Bug152` or `vendor/bin/phpunit tests/EndToEnd/Bug152`. The full suite takes ~6 minutes because end-to-end tests spawn PHPStan subprocesses.
- `castor phpstan` (alias `stan`): analyzes TwigStan itself with `phpstan-local.neon`; CI uses `--no-local`.
- `castor php-cs-fixer --no-fix`, `castor twig-cs-fixer --no-fix`, `castor editorconfig`, `castor dependency-analyzer`, `castor composer-normalize`: the CI style checks. Without `--no-fix` the two fixers rewrite files.
- `castor verify-commits`: fails on `fixup!`/`squash!` commits compared with `origin/main`.
- `castor qa`: runs the checks above.
- `castor composer:install-twig4`: tries Twig 4 (restores composer.json/lock afterwards).
- `castor demo [twigstan options]`: runs TwigStan on `demo/`, a small Symfony-routing app with deliberate template errors (`post/show.html.twig`, `errors.html.twig`). `castor demo-serve` serves it on :8080.

## Architecture

The pipeline is orchestrated by `src/Application/AnalyzeCommand.php`, which loops over several phases and repeats them when `{% include %}`d templates are discovered with new context:

1. **Context collecting**: PHPStan collectors in `src/PHPStan/Collector/` find render points (`Environment::render`, controller `render()`, `#[Template]` returned arrays) and record the context passed to each template. Includes and macro calls are collected the same way.
2. **Compilation** (`src/Processing/Compilation/`): Twig AST, Twig node visitors, Twig's own compiler, then the compiled PHP is parsed and cleaned by PHP node visitors. The collected context is injected as PHPDoc.
3. **Flattening** (`src/Processing/Flattening/`): `TwigFlattener` copies parent `doDisplay` logic and block hierarchy into each child so a template stands alone.
4. **Block context collecting** (`BlockContextCollector`) then **scope injection** (`src/Processing/ScopeInjection/`): the context before each block call is injected into the flattened PHP.
5. **Analysis**: `PHPStanRunner` runs PHPStan on the generated files as a subprocess and writes JSON; `src/PHPStan/Analysis/AnalysisResultFromJsonReader.php` reads it. `src/Error/` maps errors back to Twig source (`ErrorToSourceFileMapper`), filters false positives, collapses duplicates reported in parent templates, and applies ignores and baselines.

Original file names and line numbers must survive every transformation, since they are used to report errors. TwigStan runs PHPStan with its own extensions (`config/*.neon`, rules/type extensions in `src/PHPStan/`), and `ContainerFactory` builds a Nette DI container from the user's `twigstan.php` (`TwigStanConfig::configure(...)` in `src/Config/`). `stubs/` holds PHPStan stubs for Twig and Symfony types.

## Testing

End-to-end tests live in `tests/EndToEnd/<Case>/`: each case folder holds templates, PHP render points, a `*Test.php` extending `AbstractEndToEndTestCase` (calling `runAnalysis(__DIR__)`), and expected-result JSON (`errors.json`, `context.json`) compared by `tests/ErrorHelper.php`. To add a case, copy an existing folder such as `Bug152`. Tests use `tests/twigstan.php` and `tests/twig-loader.php`. PHPUnit is strict (`failOnRisky`, `failOnWarning`, `failOnSkipped`, `failOnIncomplete`), so a test limited to selected files fails as incomplete by design.

## Conventions

- PSR-4: `TwigStan\` maps to `src/` and, for tests, `tests/`. `TwigStan\Demo\` maps to `demo/src/`.
- Code style is `.php-cs-fixer.php` (PER-CS2.0 plus custom rules) and `.twig-cs-fixer.php`, with `.editorconfig` enforced; run the fixers before committing.
- `phpstan.neon` analyzes `bin/twigstan`, `src` and `tests` at level 8 with a baseline in `phpstan-baseline.neon`. Use of Twig internals is intentional and ignored there.
- `demo/` and `tests/` are `export-ignore`d, so they are not shipped in the Composer archive.
