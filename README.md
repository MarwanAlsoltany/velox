<p align="center">

<img width="162" height="100" src="https://raw.githubusercontent.com/MarwanAlsoltany/velox/master/themes/velox/assets/images/velox-logo.png" alt="VELOX"/>

</p>


---


<h1 align="center">VELOX</h1>

<div align="center">

The fastest way to build simple websites using PHP!


[![PHP Version][php-icon]][php-href]
[![Latest Version on Packagist][version-icon]][version-href]
[![Total Downloads][downloads-icon]][downloads-href]
[![License][license-icon]][license-href]
[![Maintenance][maintenance-icon]][maintenance-href]
[![Scrutinizer Build Status][scrutinizer-icon]][scrutinizer-href]
[![Scrutinizer Code Coverage][scrutinizer-coverage-icon]][scrutinizer-coverage-href]
[![Scrutinizer Code Quality][scrutinizer-quality-icon]][scrutinizer-quality-href]
[![Travis Build Status][travis-icon]][travis-href]
[![StyleCI Code Style][styleci-icon]][styleci-href]

[![Open in Visual Studio Code][vscode-icon]][vscode-href]

[![Tweet][tweet-icon]][tweet-href]



<details>
<summary>Table of Contents</summary>
<p>

[Installation](#installation)<br/>
[About VELOX](#about-velox)<br/>
[Architecture](#architecture)<br/>
[Config](#config)<br/>
[Classes](#classes)<br/>
[Functions](#functions)<br/>
[Themes](#themes)<br/>
[Changelog](./CHANGELOG.md)

</p>
</details>

</div>


---


## Key Features

1. Zero dependencies
2. Intuitive and easy to get along with
3. Unlimited flexibility when it comes to customizing it to your exact needs

---


## Installation

#### Using Composer:

```sh
composer create-project marwanalsoltany/velox my-velox-app
```

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *You may need to add the `--stability=dev` depending on the version/branch.*

#### Using Git:

```sh
git clone https://github.com/MarwanAlsoltany/velox.git my-velox-app
```

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *If you don't want to use any other third party packages. Installing VELOX using Git is sufficient.*

#### Using Source:

Download [VELOX](https://github.com/MarwanAlsoltany/velox/releases) as a `.zip` or `.tar.gz` and extract it in your server web root directory.

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *If you want to test out VELOX quickly and you don't have any web server available, use whatever installing method and run `php bin/app-serve` from inside VELOX directory. This command will spin up a development web server on `localhost:8000`.*


---


## About VELOX

VELOX is a lightweight micro-framework that makes creating a simple website using PHP joyful. It helps you create future-proof websites faster and more efficiently. It provides components that facilitate the process of creating a website using vanilla PHP. VELOX does not have any dependencies, the VELOX package and everything that it needs is included in the project itself. All that VELOX provides is a way to work with **config**, pass **data**, register **routes**, render **views**, handle **exceptions**, **autoload** code, and **resolve** assets. It provides the *View* and the *Controller* parts of an *MVC* design pattern, leaving the *Model* part for you to implement as you wish, or use any 3rd-Party Package; in case you needed it. VELOX can also be used as a **Static Site Generator** if all you need is HTML files in the end.

### Why does VELOX exist?

VELOX was created to solve a specific problem, it's a way to build a website that is between dynamic and static, a way to create a simple website with few pages without being forced to use a framework or a CMS that comes with a ton of stuff which will never get used, it's lightweight and straight to the point.

It's not recommended to use VELOX if you have an intermediary project, you would be better off using some well-established frameworks. VELOX is not an initiative to reinvent the wheel, you can look at VELOX as a starter-kit for small projects.

VELOX has a very special use-case, simple websites, and here is meant really simple websites. The advantage is, you don't have stuff that you don't need. Comparing VELOX to Laravel or Symfony is irrelevant, as these frameworks play in a totally different area, it also worth mentioning that VELOX is much simpler than Lumen or Slim.


---


## Architecture

### Directory structure

| Directory | Description |
| --- | --- |
| [`bootstrap`](./bootstrap) | This is where VELOX bootstraps the application. You normally don't have to change anything in this directory, unless you want to extend VELOX functionality beyond basic stuff. |
| [`config`](./config) | This is where all config files will live. All files here will be accessible using the `Config` class at runtime. |
| [`storage`](./storage) | This is where VELOX will write caches and logs. You can also use this directory to store installation-wide assets. |
| [`classes`](./classes) | This is where VELOX source files live. You shouldn't be touching anything here unless you want to make your own version of VELOX. |
| [`functions`](./functions) | This is where all functions that are loaded in the application live. You can freely add yours, or delete the entire directory.|
| [`themes`](./themes) | This is where all your frontend themes will be placed. You will be mostly working here for the frontend part of your app. |
| [`app`](./app) | This is where your own backend logic will be placed. You will be mostly working here for the backend part of your app. |
| [`bin`](./bin) | This is where PHP executables are placed. You can freely add yours, or delete the entire directory. |
| [`public`](./public) | This is where you should put your `index.php` with a symlink for static assets (active theme `assets/` directory for example) for maximum security. You can freely delete this directory if you want to. |
| [`vendor`](./vendor) | This is where your Composer dependencies will be placed. You can freely delete this directory if you don't want to use Composer. |

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *Most files listed in these directories are documented. Take a look through them to learn more about VELOX.*

### App Entry

The entry point for a VELOX app is the [`index.php`](./index.php), here you need to require the [`bootstrap/autoload.php`](./bootstrap/autoload.php), register some routes with their handlers using the `Router::class`, and start the router. This is all that you need to have a working VELOX app.

```php
<?php

require 'bootstrap/autoload.php';


Router::handle('/', function () {
    return View::render('home', ['title' => 'Home']);
});

Router::start();
```

Additionally, you can also set up handlers for `404` and `405` responses using `Router::handleRouteNotFound()` `Router::handleMethodNotAllowed()` respectively.

Alternatively, you can extract the *"routes registration part"* in its own file and let VELOX know about it using [`bootstrap/additional.php`](./bootstrap/additional.php).

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *In order for VELOX to work correctly and safely, you need to redirect all requests to application entry point (`index.php`) and block all requests to other PHP files on the server (take a look at [`.htaccess.dist`](./.htaccess.dist) to get started with Apache).*


---


## Config

The following table lists all config files that come shipped with VELOX.

| Config File | Description |
| --- | --- |
| [`global.php`](./config/global.php) | This config file contains some global variables that are used by almost all classes (app-wide config). |
| [`router.php`](./config/router.php) | This config file can be used to override `Router::class` default parameters. |
| [`theme.php`](./config/theme.php) | This config file can be used to edit/extend theme configuration. |
| [`view.php`](./config/view.php) | This config file can be used to customize everything about the views. It is used by the `View::class`. |
| [`data.php`](./config/data.php) | This config file can be used to provide any arbitrary data, which then will get injected in the `Data::class`. |
| [`cli.php`](./config/cli.php) | This config file can be used to enable/disable the commands or change their arguments. |

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *You can freely add your own config files too, all you need to do is to create a new file under `/config/` and add your configuration to it. VELOX will know about this file and load it in the application. You can access your config via `Config::get('filename.whateverKeyYouWrote')`.*


---


## Classes

The following table lists all available classes with their description.

VELOX classes are divided in four namespaces:

* [`MAKS\Velox`](./classes)
* [`MAKS\Velox\Backend`](./classes/Backend)
* [`MAKS\Velox\Frontend`](./classes/Frontend)
* [`MAKS\Velox\Helper`](./classes/Helper)

| Class | Description |
| --- | --- |
| [`Config`](./classes/Backend/Config.php) | A class that loads everything from the `/config` directory and make it as an array that is accessible via dot-notation. |
| [`Router`](./classes/Backend/Router.php) | A class that serves as a router and an entry point for the application. |
| [`Globals`](./classes/Backend/Globals.php) | A class that serves as an abstraction/wrapper to work with superglobals. |
| [`Controller`](./classes/Backend/Controller.php) | An abstract class that serves as a base Controller that can be extended to make handlers for the router. |
| [`Data`](./classes/Frontend/Data.php) | A class that serves as an abstracted data bag/store that is accessible via dot-notation. |
| [`View`](./classes/Frontend/View.php) | A class that renders view files (Layouts, Pages, and Partials) with the ability to include additional files, divide page content into sections and cache rendered views. |
| [`HTML`](./classes/Frontend/HTML.php) | A class that serves as a fluent interface to write HTML in PHP. It also helps with creating HTML elements on the fly. |
| [`Path`](./classes/Frontend/Path.php) | A class that serves as a path resolver for different paths/URLs of the app. |
| [`Dumper`](./classes/Helper/Dumper.php) | A class that dumps variables and exception in a nice formatting. |
| [`Misc`](./classes/Helper/Misc.php) | A class that serves as a holder for various miscellaneous utility function. |
| [`App`](./classes/App.php) | A class that serves as a basic service-container for VELOX. |


![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *This all what the VELOX package provides out of the box.*

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *The `App`, `Config`, `Router`, `Globals`, `Data`, `View`, `HTML`, `Path` classes are aliased on the root namespace for ease-of-use.*

### Extending VELOX

To add your own classes use the `app/` directory, this is where you should put you own business logic. Note that you have to follow PSR-4 in order for VELOX to load your classes. See [`app/Controller/DefaultController`](./app/Controller/DefaultController.php), to get an idea.


---


## Commands

VELOX comes with some handy commands that you can use to do some repetitive tasks. You can execute these commands using the `php bin/{command-name}`.

The following table lists all available commands with their description.

| Command | Description |
| --- | --- |
| [`app-serve`](./bin/app-serve) | This command starts a development server. |
| [`app-mirror`](./bin/app-mirror) | This command mirrors the app in the `/public/` directory. |
| [`config-cache`](./bin/config-cache) | This command caches the current configuration. |
| [`cache-clear`](./bin/cache-clear) | This command clears caches. |

You can customize these commands using the [`config/cli.php`](./config/cli.php) file. Here you can enable/disable them or provide different arguments for them.


---


## Functions

The following table lists all available functions and to which class/group they belong.

VELOX functions are divided into these files:

* [`helpers.php`](./functions/helpers.php): This is where helper functions for VELOX classes live, these are mainly functions that return an instance of class or alias some method on it.
* [`html.php`](./functions/html.php): This is where HTML helper functions live, these are nothing other than aliases for the most used PHP functions with HTML.

| Class/Group | Function(s) |
| --- | --- |
| `App::class` | `app()` |
| `Config::class` | `config()` |
| `Router::class` | `router()`, <br>`handle()`, `redirect()`, `forward()` |
| `Globals::class` | `globals()` |
| `View::class` | `view()`, <br>`render()`, `render_layout()`, `render_page()`, `render_partial()`, <br>`section_push()`, `section_reset()`, `section_start()`, `section_end()`, `section_yield()`, <br>`include_file()` |
| `Data::class` | `data()`, <br>`data_has()`, `data_get()`, `data_set()` |
| `HTML::class` | `html()` |
| `Path::class` | `path()`, <br>`app_path_current()`, `app_url_current()`, <br>`app_path()`, `app_url()`, <br>`theme_path()`, `theme_url()`, <br>`assets_path()`, `assets_url()` |
| `Dumper::class` | `dd()`, `dump()`, `dump_exception()` |
| HTML Helpers | `he()`, `hd()`, `hse()`, `hsd()`, `st()`, `nb()` |


![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *You can freely add your own functions too, all you need to do is to create a new file under `/functions/` and add your functions to it. VELOX will know about this file and load it in the application.*


---


## Themes

VELOX is built around the idea of `themes`, a theme is divided into four directories:

* The `assets/` directory is where all your static assets associated with this theme will be placed.
* The `layouts/` directory is where you define your layouts. A layout in VELOX terminology is the outer framing of a webpage.
* The `pages/` directory is where you put the content specific to every page, the page will then be wrapped with some layout of your choice and finally get rendered. A page in VELOX terminology is the actual content of a webpage.
* The `partials/` directory is where you put all your reusable pieces of the theme, which then will be used in your layouts, pages, or other partials. A good example for **partials** could be: *Components*, *Includes*, and *Content-Elements*.

You can customize the behavior of themes using the [`config/theme.php`](./config/theme.php) file. Here you can set the active theme with the `active` key. Themes can inherit from each other by setting parent(s) with the `parent` key. You can also change the theme directory structure if you wish to using the `paths` key. Other configurations that worth taking a look at which have to do with themes can be found in the [`config/view.php`](./config/view.php) file.

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *You can take a look at the provided [`velox`](./themes/velox) theme to see how all stuff work together in practice.*

### Examples:

1. Layout: [`themes/velox/layouts/main.phtml`](./themes/velox/layouts/main.phtml)
2. Page: [`themes/velox/pages/home.phtml`](./themes/velox/pages/home.phtml)
3. Partial: [`themes/velox/partials/text-image.phtml`](./themes/velox/partials/text-image.phtml)

---


## License

VELOX is an open-source project licensed under the [**MIT**](./LICENSE) license.
<br/>
Copyright (c) 2021 Marwan Al-Soltany. All rights reserved.
<br/>










[php-icon]: https://img.shields.io/badge/php-%3D%3C7.4-yellow?style=flat&logo=php
[version-icon]: https://img.shields.io/packagist/v/marwanalsoltany/velox.svg?style=flat&logo=packagist
[downloads-icon]: https://img.shields.io/packagist/dt/marwanalsoltany/velox.svg?style=flat&logo=packagist
[license-icon]: https://img.shields.io/badge/license-MIT-red.svg?style=flat&logo=github
[maintenance-icon]: https://img.shields.io/badge/maintained-yes-orange.svg?style=flat&logo=github
[scrutinizer-icon]: https://img.shields.io/scrutinizer/build/g/MarwanAlsoltany/velox/master?style=flat&logo=scrutinizer
[scrutinizer-coverage-icon]: https://img.shields.io/scrutinizer/coverage/g/MarwanAlsoltany/velox.svg?style=flat&logo=scrutinizer
[scrutinizer-quality-icon]: https://img.shields.io/scrutinizer/g/MarwanAlsoltany/velox.svg?style=flat&logo=scrutinizer
[travis-icon]: https://img.shields.io/travis/com/MarwanAlsoltany/velox/master.svg?style=flat&logo=travis
[styleci-icon]: https://github.styleci.io/repos/356515801/shield?branch=master&style=flat
[vscode-icon]: https://open.vscode.dev/badges/open-in-vscode.svg
[tweet-icon]: https://img.shields.io/twitter/url/http/shields.io.svg?style=social

[php-href]: https://github.com/MarwanAlsoltany/velox/search?l=php
[version-href]: https://packagist.org/packages/marwanalsoltany/velox
[downloads-href]: https://packagist.org/packages/marwanalsoltany/velox/stats
[license-href]: ./LICENSE
[maintenance-href]: https://github.com/MarwanAlsoltany/velox/graphs/commit-activity
[scrutinizer-href]: https://scrutinizer-ci.com/g/MarwanAlsoltany/velox/build-status/master
[scrutinizer-coverage-href]: https://scrutinizer-ci.com/g/MarwanAlsoltany/velox/?branch=master
[scrutinizer-quality-href]: https://scrutinizer-ci.com/g/MarwanAlsoltany/velox/?branch=maste
[travis-href]: https://travis-ci.com/MarwanAlsoltany/velox
[styleci-href]: https://github.styleci.io/repos/356515801
[vscode-href]: https://open.vscode.dev/MarwanAlsoltany/velox
[tweet-href]: https://twitter.com/intent/tweet?url=https%3A%2F%2Fgithub.com%2FMarwanAlsoltany%2Fvelox&text=The%20fastest%20way%20to%20build%20simple%20websites%20using%20%23PHP%21
