# Changelog

All notable changes to **VELOX** will be documented in this file.


## [[1.2.1] - 2021-08-11](https://github.com/MarwanAlsoltany/velox/compare/v1.2.0...v1.2.1)
- Update `App` class:
    - Add magic methods signatures in class DocBlock.
- Update `additional.php`:
    - Remove `includes` directory path.
    - Remove `includes/events` directory path.
    - Remove `includes/routes` directory path.
- Update `Controller` class:
    - Fix wrong name of the dispatched event in `__construct()` method.
- Update `Router` class:
    - Refactor `echoResponse()` method.
- Update `events/system.php`:
    - Add use statement for `Event` class to avoid class name collision.
    - Update events handling examples.
- Update `routes/web.php`:
    - Add use statement for `Router` class to avoid class name collision.

<br />

## [[1.2.0] - 2021-08-11](https://github.com/MarwanAlsoltany/velox/compare/v1.1.1...v1.2.0)
- Add `Event` class.
- Update `App` class:
    - Add new property `$event` (`Event` class).
    - Refactor `log()` method.
- Update `Controller` class:
    - Add new property `$event` (`Event` class).
- Update `Router` class:
    - Add `Event::dispatch()` calls in different methods.
    - Update `__construct()` method to add auto start functionality.
    - Refactor `echoResponse()` method to echo an auto-generated fallback pages for `404` and `405` responses.
- Update `Dumper` class:
    - Update HTML markup in `dumpException()` method.
    - Refactor `isCli()` method.
- Update `Config` class:
    - Add `Event::dispatch()` calls in different methods.
- Update `Data` class:
    - Add `Event::dispatch()` calls in different methods.
- Update `View` class:
    - Add `Event::dispatch()` calls in different methods.
- Update `helpers.php`:
    - Add `event()` function.
- Update `router.php` config file:
    - Add `allowAutoStart` config entry.
- Update `global.php` config file:
    - Add `includes` path to the available paths.
- Update `.htaccess.dist`
    - Add `includes/` to the black-listed directories
- Update `additional.php`:
    - Add `includes` directory path.
- Update `intellisense.php`:
    - Add alias for the `Event` class.
- Update `loader.php`:
    - Add `Event` class to the list of aliased classes.
    - Extract error handler and exception handler functions into variables.
    - Add shutdown function.
- Update directory structure
    - Add `includes/` directory.
- Add `events/system.php`.
- Add `routes/web.php`.
- Update `index.php`
    - Remove routes registration (moved to `/includes/routes/web.php`).
    - Add `includes` to the black-listed directories.
- Update tests:
    - Add `EventTest` class.
    - Update `RouterTest` class.
    - Fix tests namespaces to be compliant with PSR-4
- Update `composer.json`:
    - Update `branch-alias`.

<br />

## [[1.1.1] - 2021-08-10](https://github.com/MarwanAlsoltany/velox/compare/v1.1.0...v1.1.1)
- Update `composer.json`:
    - Update `branch-alias`.
    - Update `docs` link.
- Update `App` class:
    - Add `extendStatic()` method.
    - Refactor `log()` method.
- Update `Router` class:
    - Add `registerRoute()` method.
    - Refactor `handle()` and `middleware()` methods to make use of `registerRoute()`.
- Update `Globals` class:
    - Add `$globals` static property.
    - Add `$isInitialized` static property.
    - Add `instance()` method.
    - Refactor `initialize()` method.
- Update tests:
    - Add new tests to the newly created methods.
<br />

## [[1.1.0] - 2021-08-07](https://github.com/MarwanAlsoltany/velox/compare/v1.0.3...v1.1.0)
- Update `App` class:
    - Add `extend()` method.
    - Add `log()` method.
- Update `Path` class:
    - Add `normalize()` method.
- Update `Misc` class:
    - Remove `log()` method (moved to `App::log()`).
    - Remove `getNormalizedPath()` method (moved to `Path::normalize()`).
- Update `Data` class:
    - Refactor `load()` method to make `Config::$config['data']` reference `Data::$bag`.
- Update `View` class:
    - Swap usage of `Misc::log()` with `App:log()`.
    - Swap usage of `Misc::getNormalizedPath()` with `Path:normalize()`.
- Update `Config` class:
    - Refactor `include()` method to allow concatenating files and directories with the same name.
    - Refactor `include()` to exclude files that are not `.php` files.
    - Swap usage of `Misc::log()` with `App:log()`.
- Update `Router` class:
    - Swap usage of `Misc::log()` with `App:log()`.
- Update `loader.php`:
    - Swap usage of `Misc::log()` with `App:log()`.
- Update `global.php` config file:
    - Add `logging` config entry.
    - Remove `loggingEnabled` config entry.
- Update tests:
    - Add new tests to the newly created methods.

<br />

## [[1.0.3] - 2021-08-01](https://github.com/MarwanAlsoltany/velox/compare/v1.0.2...v1.0.3)
- Update `loader.php`:
    - Make autoload function use `BASE_PATH` instead of `dirname(__DIR__)`.
    - Update additional include paths array.
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
