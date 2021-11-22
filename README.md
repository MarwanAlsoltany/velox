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

[![Open in Visual Studio Code][vscode-icon]][vscode-href] [![Run on Repl.it][replit-icon]][replit-href]

[![Tweet][tweet-icon]][tweet-href] [![Star][github-icon]][github-href]



<details>
<summary>Table of Contents</summary>
<p>

[Installation](#installation)<br/>
[About VELOX](#about-velox)<br/>
[Architecture](#architecture)<br/>
[Config](#config)<br/>
[Classes](#classes)<br/>
[Functions](#functions)<br/>
[Commands](#commands)<br/>
[Themes](#themes)<br/>
[Extending VELOX](#extending-velox)<br/>
[MVC](#mvc)<br/>
[Templating](#templating)<br/>
[Authentication](#authentication)<br/>
[Changelog](./CHANGELOG.md)

</p>
</details>

<br/>

<sup>If you like this project and would like to support its development, giving it a :star: would be appreciated!</sup>

<br/>

<a href="https://replit.com/@MarwanAlsoltany/velox">
<img src="https://user-images.githubusercontent.com/7969982/133680020-48b64343-eb6e-4519-b1ba-adc86737c184.jpeg" alt="VELOX Demo" width="768" />
</a>

Check out the [Demo](https://velox.marwanalsoltany.repl.co) or play with the [REPL](https://replit.com/@MarwanAlsoltany/velox).

</div>


---


## Key Features

1. Zero dependencies
2. Minimal, intuitive and easy to get along with
3. Unlimited flexibility when it comes to customizing it to your exact needs

---


## Installation

#### Using Composer:

```sh
composer create-project marwanalsoltany/velox my-velox-app
```

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *You may need to add the `--stability=dev` depending on the version/branch. You may also want to add `--no-dev` flag to not install development dependencies.*

#### Using Git:

```sh
git clone https://github.com/MarwanAlsoltany/velox.git my-velox-app
```

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *If you don't want to use any other third party packages. Installing VELOX using Git is sufficient.*

#### Using Source:

Download [VELOX](https://github.com/MarwanAlsoltany/velox/releases) as a `.zip` or `.tar.gz` and extract it in your server web root directory.

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *If you want to test out VELOX quickly and you don't have any web server available, use whatever installing method and run `php bin/app-serve` from inside VELOX directory. This command will spin up a development web server on `localhost:8000` (Note that you need to have atleast PHP installed on your system).*


---


## About VELOX

VELOX is a lightweight micro-framework that makes creating a simple website using PHP joyful. It helps you create future-proof websites faster and more efficiently. It provides components that facilitate the process of creating a website using PHP. VELOX does not have any dependencies, the VELOX package and everything that it needs is included in the project itself. All that VELOX provides is a way to work with **config**, pass **data**, register **routes**, interact with the **database**, render **views**, handle **exceptions**, **autoload** code, and **resolve** assets. It provides the *View* and the *Controller* parts of an *MVC* design pattern. Staring from `v1.3.0`, VELOX also provides the *Model* part, making it a fully featured *MVC* framework and starting from `v1.4.0` it also comes shipped with a simple authentication system. VELOX can also be used as a **Static Site Generator** if all you need is HTML files in the end.

### Why does VELOX exist?

VELOX was created to solve a specific problem, it's a way to build a website that is between dynamic and static, a way to create a simple website with few pages without being forced to use a framework or a CMS that comes with a ton of stuff which will never get used, it's lightweight, minimal, and straight to the point.

It's not recommended to use VELOX if you have an intermediary project, you would be better off using a well-established framework. VELOX is not an initiative to reinvent the wheel, you can look at VELOX as a starter-kit for small projects.

VELOX has a very special use-case, simple websites, and here is meant really simple websites. The advantage is, you don't have stuff that you don't need. Comparing VELOX to Laravel or Symfony is irrelevant, as these frameworks play in a totally different area, it also worth mentioning that VELOX is much simpler than Lumen or Slim.


---


## Architecture

### Directory structure

| Directory | Description |
| --- | --- |
| [`bootstrap`](./bootstrap) | This is where VELOX bootstraps the application. You normally don't have to change anything in this directory, unless you want to extend VELOX functionality beyond basic stuff. |
| [`bin`](./bin) | This is where PHP executables are placed. You can freely add yours, or delete the entire directory. |
| [`app`](./app) | This is where your own backend logic will be placed. You will be mostly working here for the backend part of your app. |
| [`classes`](./classes) | This is where VELOX source files live. You shouldn't be touching anything here unless you want to make your own version of VELOX. |
| [`functions`](./functions) | This is where all functions that are loaded in the application live. You can freely add yours, or delete the entire directory.|
| [`includes`](./includes) | This is where all files that should be preloaded will be placed. You can freely add yours, or delete the entire directory.|
| [`themes`](./themes) | This is where all your frontend themes will be placed. You will be mostly working here for the frontend part of your app. |
| [`config`](./config) | This is where all config files will live. All files here will be accessible using the `Config` class at runtime. |
| [`storage`](./storage) | This is where VELOX will write caches and logs. You can also use this directory to store installation-wide assets. |
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

Additionally, you can add middlewares using `Router::middleware()` and/or set up handlers for `404` and `405` responses using `Router::handleRouteNotFound()` `Router::handleMethodNotAllowed()` respectively.

Alternatively, you can extract the *"routes registration part"* in its own file and let VELOX know about it using [`bootstrap/additional.php`](./bootstrap/additional.php).
Starting from `v1.2.0` VELOX does that by default, the file [`includes/routes/web.php`](./includes/routes/web.php) is where you should register your routes. The router will also start automatically if not started explicitly.

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *In order for VELOX to work correctly and safely, you need to redirect all requests to application entry point (`index.php`) and block all requests to other PHP files on the server (take a look at [`.htaccess.dist`](./.htaccess.dist) to get started with Apache).*


---


## Config

The following table lists all config files that come shipped with VELOX.

| Config File | Description |
| --- | --- |
| [`global.php`](./config/global.php) | This config file contains some global variables that are used by almost all classes (app-wide config). |
| [`router.php`](./config/router.php) | This config file can be used to override `Router::class` default parameters. |
| [`session.php`](./config/session.php) | This config file contains session configuration, it is used by the `Session::class`. |
| [`database.php`](./config/database.php) | This config file contains database credentials, it is used by the `Database::class`. |
| [`auth.php`](./config/auth.php) | This config file contains authentication configuration, it is used by the `Auth::class`. |
| [`theme.php`](./config/theme.php) | This config file can be used to edit/extend theme configuration. |
| [`view.php`](./config/view.php) | This config file can be used to customize everything about the views. It is used by the `View::class`. |
| [`data.php`](./config/data.php) | This config file can be used to provide any arbitrary data, which then will get injected in the `Data::class`. |
| [`cli.php`](./config/cli.php) | This config file can be used to enable/disable the commands or change their arguments. |

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *You can freely add your own config files too, all you need to do is to create a new file under `/config` and add your configuration to it. VELOX will know about this file and load it in the application. You can access your config via `Config::get('filename.whateverKeyYouWrote')`.*


---


## Classes

VELOX classes are divided in four namespaces:

* [`MAKS\Velox`](./classes)
* [`MAKS\Velox\Backend`](./classes/Backend)
* [`MAKS\Velox\Frontend`](./classes/Frontend)
* [`MAKS\Velox\Helper`](./classes/Helper)

The following table lists all available classes with their description:

| Class | Description |
| --- | --- |
| [`Velox\App`](./classes/App.php) | A class that serves as a basic service-container for VELOX. |
| <hr> | <hr> |
| [`Backend\Event`](./classes/Backend/Event.php) | A class that offers simple events handling functionality (dispatching and listening). |
| [`Backend\Config`](./classes/Backend/Config.php) | A class that loads everything from the `/config` directory and make it as an array that is accessible via dot-notation. |
| [`Backend\Router`](./classes/Backend/Router.php) | A class that serves as a router and an entry point for the application. |
| [`Backend\Globals`](./classes/Backend/Globals.php) | A class that serves as an abstraction/wrapper to work with superglobals. |
| [`Backend\Session`](./classes/Backend/Session.php) | A class that offers a simple interface to work with sessions. |
| [`Backend\Controller`](./classes/Backend/Controller.php) | An abstract class that serves as a base Controller that can be extended to make handlers for the router. |
| [`Backend\Database`](./classes/Backend/Database.php) | A class that represents the database and handles database operations. |
| [`Backend\Model`](./classes/Backend/Model.php) | An abstract class that serves as a base model that can be extended to create custom models. |
| [`Backend\Auth`](./classes/Backend/Auth.php) | A class that serves as an authentication system for users. |
| <hr> | <hr> |
| [`Frontend\Data`](./classes/Frontend/Data.php) | A class that serves as an abstracted data bag/store that is accessible via dot-notation. |
| [`Frontend\View`](./classes/Frontend/View.php) | A class that renders view files (Layouts, Pages, and Partials) with the ability to include additional files, divide page content into sections and cache rendered views. |
| [`Frontend\Engine`](./classes/Frontend/Engine.php) | A class that serves as a templating engine for view files. |
| [`Frontend\HTML`](./classes/Frontend/HTML.php) | A class that serves as a fluent interface to write HTML in PHP. It also helps with creating HTML elements on the fly. |
| [`Frontend\Path`](./classes/Frontend/Path.php) | A class that serves as a path resolver for different paths/URLs of the app. |
| <hr> | <hr> |
| [`Helper\Dumper`](./classes/Helper/Dumper.php) | A class that dumps variables and exception in a nice formatting. |
| [`Helper\Misc`](./classes/Helper/Misc.php) | A class that serves as a holder for various miscellaneous utility function. |


![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *This all what the VELOX package provides out of the box.*

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *The `App`, `Event`, `Config`, `Router`, `Globals`, `Session`, `Database`, `Auth`, `Data`, `View`, `HTML`, `Path` classes are aliased on the root namespace for ease-of-use.*


---


## Functions

VELOX functions are divided into these files:

* [`helpers.php`](./functions/helpers.php): This is where helper functions for VELOX classes live, these are mainly functions that return an instance of class or alias some method on it.
* [`html.php`](./functions/html.php): This is where HTML helper functions live, these are nothing other than aliases for the most used PHP functions with HTML.

The following table lists all available functions and to which class/group they belong:

| Class/Group | Function(s) |
| --- | --- |
| `App::class` | `app()`, <br>`abort()`, <br>`terminate()` |
| `Event::class` | `event()` |
| `Config::class` | `config()` |
| `Router::class` | `router()`, <br>`handle()`, <br>`redirect()`, <br>`forward()` |
| `Database::class` | `database()` |
| `Globals::class` | `globals()` |
| `Session::class` | `session()`, <br>`flash()`, <br>`csrf()` |
| `Auth::class` | `auth()` |
| `View::class` | `view()`, <br>`render()`, <br>`render_layout()`, <br>`render_page()`, <br>`render_partial()`, <br>`section_push()`, <br>`section_reset()`, <br>`section_start()`, <br>`section_end()`, <br>`section_yield()`, <br>`include_file()` |
| `Data::class` | `data()`, <br>`data_has()`, <br>`data_get()`, <br>`data_set()` |
| `HTML::class` | `html()` |
| `Path::class` | `path()`, <br>`app_path_current()`, <br>`app_url_current()`, <br>`app_path()`, <br>`app_url()`, <br>`theme_path()`, <br>`theme_url()`, <br>`assets_path()`, <br>`assets_url()` |
| `Dumper::class` | `dd()`, <br>`dump()`, <br>`dump_exception()` |
| HTML Helpers | `he()`, `hd()`, `hse()`, `hsd()`, `st()`, `nb()` |


![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *You can freely add your own functions too, all you need to do is to create a new file under `/functions` and add your functions to it. VELOX will know about this file and load it in the application.*


---


## Commands

VELOX comes with some handy commands that you can use to do some repetitive tasks. You can execute these commands using the `php bin/{command-name}`.

The following table lists all available commands with their description.

| Command | Description |
| --- | --- |
| [`app-serve`](./bin/app-serve) | This command starts a development server. |
| [`app-mirror`](./bin/app-mirror) | This command mirrors the application in the `/public` directory. |
| [`config-cache`](./bin/config-cache) | This command caches the current configuration. |
| [`config-dump`](./bin/config-dump) | This command dumps the current configuration with syntax highlighting. |
| [`cache-clear`](./bin/cache-clear) | This command clears caches. |

You can customize these commands using the [`config/cli.php`](./config/cli.php) file. Here you can enable/disable them or provide different arguments for them.

If you would like to make all these commands accessible via a single interface. Check out my other package [Blend](https://github.com/MarwanAlsoltany/blend), which will do that for you and even more.


---


## Themes

VELOX is built around the idea of <b><u><i>themes</i></u></b>, a theme is divided into four directories:

* The [`assets/`](./themes/velox/assets) directory is where all your static assets associated with this theme will be placed.
* The [`layouts/`](./themes/velox/layouts) directory is where you define your layouts. A layout in VELOX terminology is the outer framing of a webpage.
* The [`pages/`](./themes/velox/pages) directory is where you put the content specific to every page, the page will then be wrapped with some layout of your choice and finally get rendered. A page in VELOX terminology is the actual content of a webpage.
* The [`partials/`](./themes/velox/partials) directory is where you put all your reusable pieces of the theme, which then will be used in your layouts, pages, or other partials. A good example for **partials** could be: *Components*, *Includes*, and *Content-Elements*.

You can customize the behavior of themes using the [`config/theme.php`](./config/theme.php) file. Here you can set the active theme with the `active` key. Themes can inherit from each other by setting parent(s) with the `parent` key. You can also change the theme directory structure if you wish to using the `paths` key. Other configurations (caching for example) that worth taking a look at which have to do with themes can be found in the [`config/view.php`](./config/view.php) file.

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *You can take a look at the provided [`velox`](./themes/velox) theme to see how all stuff work together in practice.*

### Examples:

1. [Layout](./themes/velox/layouts): [`themes/velox/layouts/main.phtml`](./themes/velox/layouts/main.phtml)
2. [Page](./themes/velox/pages): [`themes/velox/pages/home.phtml`](./themes/velox/pages/home.phtml)
3. [Partial](./themes/velox/partials): [`themes/velox/partials/text-image.phtml`](./themes/velox/partials/text-image.phtml)



---


## Extending VELOX

To add your own classes use the `app/` directory, this is where you should put you own business logic. Note that you have to follow [PSR-4](https://www.php-fig.org/psr/psr-4/) in order for VELOX to load your classes. See [`app/Controller/DefaultController.php`](./app/Controller/DefaultController.php), to get an idea.

Here is a list of some important files that you should consider when working with VELOX:
* Preloading files/directories [`autoload/additional.php`](./autoload/additional.php).
* Providing additional data [`config/data.php`](./config/data.php).
* Registering web routes [`includes/routes/web.php`](./includes/routes/web.php) (starting from `v1.2.0`).
* Registering event handlers [`includes/events/system.php`](./includes/events/system.php) (starting from `v1.2.0`).



---


## MVC

### Creating a Model:
```php
<?php

namespace App\Model;

use MAKS\Velox\Backend\Model;

class Person extends Model
{
    protected static ?string $table = 'persons';
    protected static ?array $columns = ['id', 'first_name', 'last_name', 'age', ...];
    protected static ?string $primaryKey = 'id';

    public static function schema(): string
    {
        // return SQL to create the table
    }
}
```

### Working with the Model:

```php
<?php

use App\Model\Person;

// creating/manipulating models
$person = new Person(); // set attributes later via setters or public assignment.
$person = new Person(['first_name' => $value, ...]); // set attributes in constructor
$person->get('first_name'); // get an attribute
$person->set('last_name', $value); // set an attribute
$person->getFirstName(); // case will be changed to 'snake_case' automatically.
$person->setLastName($value); // case will be changed to 'snake_case' automatically.
$person->firstName; // case will be changed to 'snake_case' automatically.
$person->lastName = $value; // case will be changed to 'snake_case' automatically.
$attributes = $person->getAttributes(); // returns all attributes.
$person->save(); // persists the model in the database.
$person->update(['first_name' => $value]); // updates the model and save changes in the database.
$person->delete(); // deletes the model from the database.
Person::create($attributes); // creates a new model instance, call save() on the instance to save it in the database.
Person::destroy($id); // destroys a model and deletes it from the database.

// fetching models
$count   = Person::count(); // returns the number of models in the database.
$person  = Person::first();
$person  = Person::last();
$person  = Person::one(['first_name' => 'John']);
$persons = Person::all(['last_name' => 'Doe'], $order, $offset, $limit);
$person  = Person::find($id); // $id is the primary key of the model.
$persons = Person::find('first_name', 'John', 'last_name', 'Doe' ...); // or
$persons = Person::find(['first_name' => 'John', 'last_name' => 'Doe']);
$persons = Person::findByFirstName('John'); // fetches using an attribute, case will be changed to 'snake_case' automatically.
$persons = Person::where('first_name', '=', $value); // fetches using a where clause condition.
$persons = Person::where('last_name', 'LIKE', '%Doe', [['AND', 'age', '>', 27], ...], 'age DESC', $limit, $offset);
$persons = Person::fetch('SELECT * FROM @table WHERE `first_name` = ?', [$value]); // fetch using raw SQL query.

```

### Using the Model in the Controller:

```php
<?php

namespace App\Controller;

use MAKS\Velox\Backend\Controller;
use App\Model\Person;

class PersonsController extends Controller
{
    public function indexAction()
    {
        $persons = Person::all();

        return $this->view->render('persons/index', [
            'title' => 'Persons',
            'persons' => $persons
        ]);
    }

    // other CRUD actions ...

    /**
     * Persons search action.
     * @route("/persons/search", {GET})
     */
    public function searchAction()
    {
        // ...
    }

    /**
     * Persons middleware.
     *
     * @route("/persons/*", {GET, POST})
     */
    public function personsMiddleware()
    {
        // ...
    }
}
```

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *CRUD operations (namely: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`) are registered and configured by default. To register your own routes automatically, use the `@route("<path>", {<http-verb>, ...})` annotation. See `Controller::registerRoutes()` DocBlock to learn more.*

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *To make the model available as property for the controller (`$this->model`), use `Controller::associateModel()`. See `Controller::associateModel()` DocBlock to learn more.*

### Using the Model in a View:

```html
{# theme/pages/persons/index.phtml #}

{! @extends 'theme/pages/persons/base' !}

{! @block content !}
    {! @super !}

    <h1>{{ $title }}</h1>

    {! @if (isset($persons) && count($persons)) !}
        <ul>
            {! @foreach ($persons as $person) !}
                <li>{{ $person->firsName }} {{ $person->lastName }}</li>
            {! @endforeach !}
        </ul>
    {! @endif !}
{! @endblock !}
```

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *Check out the [`Person`](./app/Model/Person.php) model and the [`PersonsController`](./app/Controller/PersonsController.php) to see a realistic example.*


---


## Templating

VELOX comes with its own templating engine. This templating engine is very intuitive and easy to get along with, if you have experience with any other templating engine, learning it would be a matter of minutes. Note that the use of this templating engine is optional. You can simply use raw PHP in your views.

The following table lists all available tags and what they do:

| Tag | Description |
| --- | --- |
| <code>{!&nbsp;@extends&nbsp;'path/to/template'&nbsp;!}</code> | Extend a template, blocks of this template will be inherited. |
| <code>{!&nbsp;@include&nbsp;'path/to/file'&nbsp;!}</code> | Include a file, this will get rendered before inclusion (can't access context variables). |
| <code>{!&nbsp;@embed&nbsp;'path/to/file'&nbsp;!}</code> | Embed a file, this will be included as is (can access context variables). |
| <code>{!&nbsp;@block&nbsp;name&nbsp;!}</code><br><code>{!&nbsp;@endblock&nbsp;!}</code> | Create a block to wrap some code. |
| <code>{!&nbsp;@super&nbsp;!}</code> | Use it inside a block in an extended template to inherit parent block content. |
| <code>{!&nbsp;@block(name)&nbsp;!}</code> | Print a block. Needs to be called at least once in order to render a block. |
| <code>{!&nbsp;@foreach&nbsp;($vars as $var)&nbsp;!}</code><br><code>{!&nbsp;@endforeach&nbsp;!}</code> | Control structures (loops, if statements, ...). All PHP control structures are available (`if`, `else`, `elseif`, `do`, `while`, `for`, `foreach`, `continue`, `switch`, `break`, `return`, `require`, `include`) with the same syntax but simply prefixed with an `@` symbol if a control structure is the first word in the tag. |
| <code>{!&nbsp;$var&nbsp;=&nbsp;''&nbsp;!}</code> | Variable assignments. Content can be a variable or any valid PHP expression. |
| <code>{{&nbsp;$var&nbsp;}}</code> | Print a variable. Content can be a variable or any PHP expression that can be casted to a string. |
| <code>{{{&nbsp;$var&nbsp;}}}</code> | Print a variable without escaping. Content can be a variable or any PHP expression that can be casted to a string. |
| <code>{#&nbsp;This&nbsp;is&nbsp;a&nbsp;comment&nbsp;#}</code> | Comment something. This will be a PHP comment (will not be available in final HTML). |

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *Take a look at [`persons`](themes/velox/pages/persons) views of [`PersonsController`](./app/Controller/PersonsController.php) in VELOX theme for a realistic example.*


---


## Authentication

Starting from `v1.4.0` VELOX comes shipped with a simple built-in authentication system. This system is very simple and easy to use.

```php
use MAKS\Velox\Backend\Auth;

// instantiate the Auth class
$auth = new Auth(); // or Auth::instance();

// register a new user
$status = $auth->register('username', 'password');

// unregister a user
$status = $auth->unregister('username');

// log in a user
$status = $auth->login('username', 'password');

// log out a user
$auth->logout();

// authenticate a user model
Auth::authenticate($user);

// check if there is a logged in user
$status = Auth::check();

// retrieve the current authenticated user
$user = Auth::user();

// add HTTP basic auth
Auth::basic(['username' => 'password']);
```

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *Check out the [`UsersController`](./app/Controller/UsersController.php) to see a realistic example.*


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
[replit-icon]: https://repl.it/badge/github/MarwanAlsoltany/velox
[tweet-icon]: https://img.shields.io/twitter/url/http/shields.io.svg?style=social
[github-icon]: https://img.shields.io/github/stars/MarwanAlsoltany/velox.svg?style=social&label=Star

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
[replit-href]: https://replit.com/@marwanalsoltany/velox
[tweet-href]: https://twitter.com/intent/tweet?url=https%3A%2F%2Fgithub.com%2FMarwanAlsoltany%2Fvelox&text=The%20fastest%20way%20to%20build%20simple%20websites%20using%20%23PHP%21
[github-href]: https://github.com/MarwanAlsoltany/velox/stargazers
