---
description: PHPUnit, PHPStan, and PHPCS conventions for the Obsidian Flame theme plugin
globs: ["src/**/*.php", "tests/**/*.php", "dev-stubs/**/*.php"]
---

# Testing and analysis

- Run the suite with `vendor/bin/phpunit`. `phpunit.xml` boots `tests/bootstrap.php` and collects `tests/` files with a `Test.php` suffix into the `unit` suite; coverage is limited to `src`.
- Run PHPStan as `vendor/bin/phpstan analyse -c phpstan.neon`, not `phpstan analyse src` — the config's `scanDirectories` is what pulls in `dev-stubs/`. Level is `9`.
- Run PHPCS as `vendor/bin/phpcs --standard=phpcs.xml`, not `phpcs src` — the ruleset also covers `tests/` and `dev-stubs/`. Standard is `PSR12`, so every PHP file ends with a single trailing newline.
- `composer phpstan` and `composer phpcs` wrap the two commands above.
- `.github/workflows/test.yml` runs all three on PHP `8.3` and `8.4`.

# Host contracts and stubs

- `Phlix\Shared\Plugin\LifecycleInterface` ships in `detain/phlix-shared`; `Phlix\Theming\ThemeSourceInterface` is declared by the server and is in no Composer package.
- `tests/bootstrap.php` requires `dev-stubs/LifecycleInterface.php` and `dev-stubs/ThemeSourceInterface.php` only when `interface_exists()` returns false, so a run inside a Phlix server checkout still exercises the real contracts.
- `dev-stubs/` is intentionally tracked in git (see `.gitignore`) because PHPStan needs it; never autoload it in production.
- New tests belong in `tests/` under the `Phlix\ObsidianFlame\Tests\` PSR-4 namespace.
