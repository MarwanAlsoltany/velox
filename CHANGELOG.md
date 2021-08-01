# Changelog

All notable changes to **VELOX** will be documented in this file.


## [[1.0.3] - 2021-08-01](https://github.com/MarwanAlsoltany/velox/compare/v1.0.2...v1.0.3)
- Update `loader.php`:
    - Make autoload function use `BASE_PATH` instead of `dirname(__DIR__)`
    - Update additional include paths array
- Update `Config` class:
    - Refactor `parse()` method to allow referencing items of all data types.
    - Refactor `include()` method to skip files/directories that have dots in their names as this will conflict with array access separator.
- Update `Misc` class:
    - Add `getObjectProperty()` method.
    - Add `setObjectProperty()` method.
    - Add `callObjectMethod()` method.
- Update `Dumper` class:
    - Add support for syntax highlighting in the CLI.
    - Refactor various class methods.
- Add `config-dump` command.

<br />

## [[1.0.2] - 2021-07-11](https://github.com/MarwanAlsoltany/velox/compare/v1.0.1...v1.0.2)
- Update `composer.json`:
    - Add required PHP extensions `ext-json` and `ext-dom`.
- Update `app-mirror` command:
    - Fix file permissions issues with generated files/directories.
    - Fix platform related issue when removing old links.
- Update `app-serve` command:
    - Remove `declare(ticks=1)` execution directive that was introduced by mistake.

<br />

## [[1.0.1] - 2021-07-11](https://github.com/MarwanAlsoltany/velox/compare/v1.0.0...v1.0.1)
- Update `autoload.php`:
    - Use `require()` to load `./loader.php` instead of `include()`.
- Update `Globals` class:
    - Make the class final.
- Update `Config` class:
    - Change class constants from protected to public.
    - Change `$config` property from private to protected.
    - Update `parse()` method to make it parse for the count of config files.
- Update `View` class:
    - Change class constants from protected to public.
- Update `Data` class:
    - Make use of `Globals` class instead of direct access to superglobals.
- Update `Dumper` class:
    - Refactor `dumpException()` method.
    - Update `exportExpression()` array construct to square brackets regex.
- Update `global.php` config file:
    - Add `public` path to the available paths.
- Add `cli.php` config file.
- Update all commands to make use of `cli.php` config file.
- Update `cache-config` command:
    - Rename `cache-config` to `config-cache`.
- Add `app-mirror` command:
    - Rename `cache-config` to `config-cache`.
- Fix typos and update DocBlocks:
    - Fix some typos in DocBlocks and other parts of the codebase.

<br />

## [[1.0.0] - 2021-06-27](https://github.com/MarwanAlsoltany/velox/compare/v1.0.0-rc...v1.0.0)
- Initial release.

<br />

## [[1.0.0-rc] - 2021-05-03](https://github.com/MarwanAlsoltany/velox/compare/v1.0.0-beta...v1.0.0-rc)
- Release candidate.

<br />

## [[1.0.0-beta] - 2021-04-12](https://github.com/MarwanAlsoltany/velox/commits/v1.0.0-beta)
- Beta release.

<br />

## [Unreleased]

<br />
