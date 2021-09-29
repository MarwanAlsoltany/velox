# Changelog

All notable changes to **VELOX** will be documented in this file.

<br />

## [[1.3.0] - 2021-09-29](https://github.com/MarwanAlsoltany/velox/compare/v1.2.5...v1.3.0)

- Add `Session` class.
- Add `Database` class.
- Add `Model` class.
- Add `Engine` class.
- Add `Person` class.
- Add `PersonsController` class.
- Update `App` class:
    - Add `$session` property.
    - Add `$database` property.
    - Update `abort()` method to not clear all opened buffers.
    - Update `terminate()` method to rename `UNIT_TESTING` constant to `EXIT_EXCEPTION`.
- Update `Controller` class:
    - Add `$crudRoutes` property.
    - Add `$session` property.
    - Add `$database` property.
    - Add `$model` property.
    - Add `associateModel()` and `doAssociateModel()` methods.
    - Add `registerRoutes()` and `doRegisterRoutes()` methods.
- Update `Router` class:
    - Update `redirect()` method.
    - Update `forward()` method.
    - Update `start()` method to check fot CSRF.
    - Update `doEchoResponse()` method to fall back to error pages in config.
- Update `View` class:
    - Add `parse()` method.
    - Update `compile()` method.
    - Update `require()` method.
    - Update `clearCache()` method.
    - Add `engine` config default in `DEFAULTS` constant.
- Update `Misc` class:
    - Add `transform()` method.
- Update `Globals` class:
    - Update `initialize()` method to replace direct use of session function with `Session` class.
- Update `TestCase` class:
    - Rename `UNIT_TESTING` constant to `EXIT_EXCEPTION`.
- Update tests:
    - Add `SessionTest` class.
    - Add `DatabaseTest` class.
    - Add `ModelTest` class.
    - Add `EngineTest` class.
    - Add `DatabaseMock` class.
    - Add `ModelMock` class.
    - Add `ControllerMock` class.
    - Add `TestObjectMock` class.
    - Update `AppTest` class.
    - Update `ControllerTest` class.
    - Update `ViewTest` class.
    - Update `MiscTest` class.
- Update `loader.php`:
    - Add `Session` class to the list of aliased classes.
    - Add `Database` class to the list of aliased classes.
    - Update exception handler function.
- Update `intellisense.php`:
    - Add alias for the `Session` class.
    - Add alias for the `Database` class.
- Update `helper.php`:
    - Add `session()` function
    - Add `flash()` function
    - Add `csrf()` function
    - Add `database()` function
- Add `session.php` config file.
- Add `database.php` config file.
- Update `global.php` config file:
    - Add `errorPages` config entry
    - Remove `errorPage` config entry
- Update `view.php` config file:
    - Add `engine` config entry
- Update `routes/web.php`:
    - Add instantiation for `PersonsController`.
    - Update error pages routes and demo routes documentation.
- Update `velox` theme:
    - Update `navigation.phtml` partial.
    - Add `500.phtml`.
    - Add `403.phtml`.
    - Update `404.phtml`.
    - Update `405.phtml`.
    - Add `PersonsController` views.
- Update `composer.json`:
    - Add new required PHP extensions `ext-pdo` and `ext-intl`.
    - Update `branch-alias`.
    - Update `keywords`.
- Update `.travis.yml`:
    - Add database config.
<br />

## [[1.2.5] - 2021-09-26](https://github.com/MarwanAlsoltany/velox/compare/v1.2.4...v1.2.5)

- Update `App` class:
    - Add `abort()` method.
    - Add `terminate()` method.
- Update `Globals` class:
    - Add `cut()` method.
    - Fix an issue in `initialize()` method with `$_SESSION` reference.
- Update `Router` class:
    - Update `getRequestMethod()` method to remove `_method` variable from `$_POST`.
    - Refactor `doEchoResponse()` method.
- Update `View` class:
    - Remove `VIEWS_CACHE_DIR` class constant (config is used now instead).
    - Update `cache()` method to replace `VIEWS_CACHE_DIR` with value from config.
    - Update `cacheClear()` method to replace `VIEWS_CACHE_DIR` with value from config.
    - Update `resolveCachePath()` method to replace `VIEWS_CACHE_DIR` with value from config.
    - Update `include()` method to accept a parameter for variables.
    - Update `require()` method to minimize variables leaking into the view.
    - Refactor `compile()` method.
- Update `HTML` class:
    - Update `minify()` method.
- Update `Path` class:
    - Fix an issue with regex in `normalize()` method.
- Update `Misc` class:
    - Add `cutArrayValueByKey()` method.
- Update `Dumper` class:
    - Refactor `dumpException()` method to decode HTML in stack trace function arguments.
- Update `loader.php`:
    - Update exception handler function.
- Update `helpers.php`:
    - Add `abort()` function.
    - Add `terminate()` function.
- Update `html.php`:
    - Add `string` casting to functions parameters.
- Update `TestCase` class:
    - Add `UNIT_TESTING` constant.
- Update tests:
    - Update `GlobalsTest` class.
    - Update `MiscTest` class.
    - Update `RouterTest` class.

<br />

## [[1.2.4] - 2021-09-16](https://github.com/MarwanAlsoltany/velox/compare/v1.2.3...v1.2.4)
- Update `velox` theme:
    - Replace filler text in pages with actual text.
- Update `global.php` config file:
    - Add `baseUrl` config entry.
- Update `theme.php` config file:
    - Update `paths`.
- Update `Router` class:
    - Update `redirect()` method to make use of 'baseUrl' config value.
- Update `Path` class:
    - Update `resolveUrl()` method to make use of 'baseUrl' config value.
- Update `Misc` class:
    - Fix an issue in `getArrayValueByKey()` method with default return value.
- Update `HTML` class:
    - Update `minify()` method to fix invalid HTML minification.
- Update tests:
    - Update `GlobalsTest` class.
    - Update `HTMLTest` class.

<br />

## [[1.2.3] - 2021-08-26](https://github.com/MarwanAlsoltany/velox/compare/v1.2.2...v1.2.3)
- Update `Globals` class:
    - Update `__get()` magic method.

<br />

## [[1.2.2] - 2021-08-12](https://github.com/MarwanAlsoltany/velox/compare/v1.2.1...v1.2.2)
- Update `Router` class:
    - Refactor `echoResponse()` method.
    - Rename `echoResponse()` method to `doEchoResponse()`.
    - Update `start()` method to make use of `doEchoResponse()` method.
- Update `Dumper` class:
    - Update `dd()` method to make it skip shutdown function.
- Update `loader.php`:
    - Add a check for `$GLOBALS['_DIE']` in shutdown function to allow for exiting the script.
- Update `events/system.php`:
    - Remove use statement for `Event` class.
    - Update events handling examples.
- Update `routes/web.php`:
    - Remove use statement for `Router` class.
    - Replace `hse()` function call with `htmlspecialchars()`.
- Update tests:
    - Update `RouterTest` class.

<br />

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
