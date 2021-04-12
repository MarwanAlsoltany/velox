<p align="center">

<img width="223" height="144" src="https://raw.githubusercontent.com/MarwanAlsoltany/velox/master/themes/velox/assets/images/velox-logo.png" alt="VELOX"/>

</p>


---


<h1 align="center">VELOX</h1>

<div align="center">

The fastest way to build simple websites using PHP!


[![PHP Version][php-icon]][php-href]
[![Latest Version on Packagist][version-icon]][version-href]
[![License][license-icon]][license-href]
[![Maintenance][maintenance-icon]][maintenance-href]
[![Total Downloads][downloads-icon]][downloads-href]
[![Scrutinizer Build Status][scrutinizer-icon]][scrutinizer-href]
[![Scrutinizer Code Quality][scrutinizer-quality-icon]][scrutinizer-quality-href]
[![StyleCI Code Style][styleci-icon]][styleci-href]


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

<a href="https://twitter.com/intent/tweet?url=https%3A%2F%2Fgithub.com%2FMarwanAlsoltany%2Fvelox&text=The%20fastest%20way%20to%20build%20simple%20websites%20using%20%23PHP%21" title="Tweet" target="_blank"><img src="https://img.shields.io/twitter/url/http/shields.io.svg?style=social" alt="Tweet"></a>
</div>



---


<p align="center">



</p>


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

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *You may need to add the `--stability=dev ` depending on the version/branch.*

#### Using Git:

```sh
git clone https://github.com/MarwanAlsoltany/velox.git my-velox-app
```

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *If you don't want to use any other third party packages. Installing VELOX using Git is sufficient.*

#### Using source:

Download [VELOX](https://github.com/MarwanAlsoltany/velox/releases) as a `.zip` or `.tar.gz` and extract it in your server web root directory.


---


## About VELOX

VELOX is a lightweight micro-framework that makes creating a simple website using PHP joyful. It helps you create future-proof websites faster and more efficiently. It provides components that facilitate the process of creating a website using vanilla PHP. VELOX does not have any dependencies, the VELOX package and everything that it needs is included in the project itself. All that VELOX provides is a way to work with `config`, pass `data`, register `routes`, render `views`, handle `exceptions`, `autoload` code, and `resolve` assets. It provides the *View* and the *Controller* parts of an *MVC* design pattern, leaving the *Model* part for you to implement as you wish in case you needed it. VELOX can also be used as a **Static Site Generator** if all you need is HTML files in the end.


---


## Architecture

### Directory structure

| Directory | Description |
| --- | --- |
| [`bootstrap`](./bootstrap) | This is where VELOX bootstraps the application. You normally don't have to change anything in this directory, unless you want to extend VELOX functionality beyond basic stuff. |
| [`config`](./config) | This is where all config files will live. All files here will be accessible using the `Config` class at runtime. |
| [`storage`](./storage) | This is where VELOX will write caches and logs. You can also use this directory to store installation-wide assets. |
| [`classes`](./classes) | This is where VELOX source files live. You shouldn't be touching anything here unless you want to make your own version of VELOX. |
| [`functions`](./functions) | This where all functions that are loaded in the application live. You can freely add yours, or delete the entire directory |
| [`themes`](./themes) | This where all your frontend themes will be placed. You will be mostly working here for the frontend part of the app. |
| [`app`](./app) | This where your own backend logic will be placed. You will be mostly working here for the backend part of the app. |
| [`bin`](./bin) | This is where PHP executables are placed. You can freely add yours, or delete the entire directory. |
| [`public`](./public) | This is where you should put your `index.php` for maximum security. You can freely delete this directory if you want to. |
| [`vendor`](./vendor) | This where your composer dependencies will find their place. You can freely delete this directory if you don't want to use composer. |

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *Most files listed in these directories are documented. Take a look through them to learn more about VELOX.*

### App Entry

The entry point for a VELOX app is the `index.php`, here you need to require the `bootstrap/autoload.php`, register some routes with their handlers using the `Router` class, and start the router. This is all that you need to have a working VELOX app.

```php
<?php

require_once 'bootstrap/autoload.php';

Router::handle('/', function () {
    return View::render('home', ['title' => 'Home']);
});

Router::start();
```

Additionally, you can also set up handlers for `404` and `405` responses using `Router::handleRouteNotFound()` `Router::handleMethodNotAllowed()` respectively.

Alternatively, you can extract the *"routes registration part"* in its own file and let VELOX know about it using `bootstrap/additional.php`

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *In order for VELOX to work correctly and safely, you need to redirect all requests to the application entry point (`index.php`) and block all requests to other PHP files on the server (take a look at `.htaccess.dist` to get started with Apache).*


---


## Config

The following table lists all config files that come shipped with VELOX.

| Config File | Description |
| --- | --- |
| [`global.php`](./config/global.php) | This config file contains some global variables that are used by almost all classes (app-wide config). |
| [`router.php`](./config/router.php) | This config file can be used to override `Router::class` default parameters. |
| [`theme.php`](./config/theme.php) | This config file can be used to edit/extend theme configuration. |
| [`view.php`](./config/view.php) | This config file can be used to customize everything about the views. It is used by the `View::class`. |
| [`data.php`](./config/data.php) | This config file can be used to provide any arbitrary data, this will then be injected in the `Data::class`. |

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *You can freely add your own config files too, all you need to do is to create a new file under `/config` and add you own configuration in it. VELOX will know about this file and load it in the application. You can access your config via `Config::get('filename.whatEverKeyYouWrote')`.*


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
| [Config](./classes/Backend/Config.php) | A class that loads everything from the `/config` directory and make it as an array that is accessible via dot-notation. |
| [Router](./classes/Backend/Router.php) | A class that serves as a router and an entry point for the application. |
| [Controller](./classes/Backend/Controller.php) | An abstract class that serves as a base Controller that can be extended to make handlers for the router. |
| [Data](./classes/Frontend/Data.php) | A class that serves as an abstracted data bag/store that is accessible via dot-notation. |
| [View](./classes/Frontend/View.php) | A class that renders and caches view files (Layouts, Pages, and Partials) with the ability to include additional files and divide page content into sections. |
| [HTML](./classes/Frontend/HTML.php) | A class that serves as a fluent interface to write HTML in PHP. It also helps with creating HTML elements on the fly. |
| [Path](./classes/Frontend/Path.php) | A class that serves as a path resolver for different paths/URLs of the app. |
| [Dumper](./classes/Helper/Dumper.php) | A class that dumps variables and exception in a nice formatting. |
| [Misc](./classes/Helper/Misc.php) | A class that serves as a holder for various miscellaneous utility function. |
| [App](./classes/App.php) | A class that serves as a basic service-container for VELOX. |


![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *This all what the VELOX package provides out of the box.*

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *The `App`, `Config`, `Router`, `Data`, `View`, `HTML`, `Path` classes are aliased on the root namespace for ease-of-use.*

### Extending VELOX

To add you own classes use the `app/` directory, this is where you should put you own business logic. Note that you have to follow PSR-4 in order for VELOX to load you classes. See [`app/Controller/DefaultController`](./app/Controller/DefaultController.php), to get an idea.

---


## Functions

The following table lists all available functions and to which class they belong.

VELOX functions are divided into these files:

* [`helper.php`](./functions/helper.php): This is where helper functions for VELOX classes live, these are mainly functions that return an instance of class or alias some method on it.
* [`html.php`](./functions/html.php): This is where HTML helper functions live, these are nothing other than aliases for the most used PHP functions with HTML.

| Class/Group | Function(s) |
|-|-|
| `App::class` | `app()` |
| `Config::class` | `config()` |
| `Router::class` | `router()`, <br>`handle()`, `redirect()`, `forward()` |
| `View::class` | `view()`, <br>`render()`, `render_layout()`, `render_page()`, `render_partial()`, <br>`section_push()`, `section_reset()`, `section_start()`, `section_end()`, `section_yield()`, `include_file()` |
| `Data::class` | `data()`, <br>`data_has()`, `data_get()`, `data_set()` |
| `HTML::class` | `html()` |
| `Path::class` | `path()`, <br>`app_path_current()`, `app_url_current()`, <br>`app_path()`, `app_url()`, <br>`theme_path()`, `theme_url()`, <br>`assets_path()`, `assets_url()` |
| `Dumper::class` | `dd()`, `dump()`, `dump_exception()` |
| HTML Helpers | `he()`, `hd()`, `hse()`, `hsd()`, `st()`, `nb()` |


![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *You can freely add your functions too, all you need to do is to create a new file under `functions/` and add you own functions in it. VELOX will know about this file and load it in the application.*


---


## Themes

VELOX is built around the idea of `themes`, a theme is divided into four directories:
* The `assets/` directory is where all your static assets associated with this theme will find their place
* The `layouts/` directory is where you define your layouts. A layout in VELOX terminology is the outer framing of a webpage
* The `pages/` directory is where you put the content specific to every page, the page will then be wrapped with some layout of your choice and finally rendered. A page in VELOX terminology is the actual content of a webpage
* The `partials/` directory is where you put all your reusable pieces of the theme, which then will be used in your layouts or pages. A good example for **partials** could be: *Components*, *Includes*, and *Content-Elements*

You can customize the behavior of themes using the `config/theme.php` file. Here you can set the active theme with the `active` key. Themes can inherit from each other by setting parent(s) with the `parent` key. You can also change the theme directory structure if you wish to using the `paths` key. Other configurations that worth taking a look at which have to do with themes can be found in the `config/view.php` file.

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *You can take a look at the provided [`velox`](./themes/velox) theme to see how all stuff work together in practice.*

### Examples:
1. Layout: [`themes/velox/layouts/main.phtml`](./themes/velox/layouts/main.phtml)
2. Page: [`themes/velox/pages/home.phtml`](./themes/velox/pages/home.phtml)
3. Partial: [`themes/velox/partials/text-image.phtml`](./themes/velox/partials/text-image.phtml)


---


## License

VELOX is an open-sourced project licensed under the [**MIT**](./LICENSE) license.
<br/>
Copyright (c) 2021 Marwan Al-Soltany. All rights reserved.
<br/>










[php-icon]: https://img.shields.io/badge/php-%3D%3C7.4-yellow?style=flat-square
[version-icon]: https://img.shields.io/packagist/v/marwanalsoltany/velox.svg?style=flat-square
[license-icon]: https://img.shields.io/badge/license-MIT-red.svg?style=flat-square
[maintenance-icon]: https://img.shields.io/badge/maintained-yes-orange.svg?style=flat-square
[downloads-icon]: https://img.shields.io/packagist/dt/marwanalsoltany/velox.svg?style=flat-square
[scrutinizer-icon]: https://img.shields.io/scrutinizer/build/g/MarwanAlsoltany/velox/master?style=flat-square
[scrutinizer-quality-icon]: https://img.shields.io/scrutinizer/g/MarwanAlsoltany/velox.svg?style=flat-square
[styleci-icon]: https://github.styleci.io/repos/356515801/shield?branch=master

[php-href]: https://github.com/MarwanAlsoltany/velox/search?l=php
[version-href]: https://packagist.org/packages/marwanalsoltany/velox
[license-href]: ./LICENSE
[maintenance-href]: https://github.com/MarwanAlsoltany/velox/graphs/commit-activity
[downloads-href]: https://packagist.org/packages/marwanalsoltany/velox/stats
[scrutinizer-href]: https://scrutinizer-ci.com/g/MarwanAlsoltany/velox/build-status/master
[scrutinizer-quality-href]: https://scrutinizer-ci.com/g/MarwanAlsoltany/velox/?branch=maste
[styleci-href]: https://github.styleci.io/repos/356515801
