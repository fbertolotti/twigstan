# TODO

## Support Twig 4 and add it to the CI matrix

The CI only tests Twig 3 (`twig: [ 3 ]` in `.github/workflows/ci.yaml`). The "Setup Twig 4" step is already
there, but it never runs.

Status, checked with `twig/twig:4.x-dev as 3.9999` on PHP 8.4 (`castor composer:install-twig4`, then the full
`vendor/bin/phpunit`): 127 tests, 28 errors. The unit suite passes, the failures are all end-to-end tests,
including `DemoTest`.

- 27 errors: `LogicException: Attribute "is_defined_test" does not exist for Node "ContextVariable"`, thrown by
    `src/Processing/Compilation/TwigVisitor/MarkNameExpressionAsAlwaysDefinedVisitor.php`. Twig 4 no longer has this
    attribute on `ContextVariable` nodes, so find out how Twig 4 marks an `is defined` test.
- 1 error: `SyntaxError: Could not parse component name` in `tests/EndToEnd/Component/embed.twig`.
- More errors may show up once the first one is fixed.

When the suite is green:

- Change the matrix to `twig: [ 3, 4 ]`.
- Exclude `dependency-versions: lowest` for Twig 4, because `4.x-dev as 3.9999` makes no sense with the lowest
    dependencies.
- Add `errors.v4.json` files where the expected errors differ (`ErrorHelper` already supports them).
