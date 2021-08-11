<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 *  This file can be used to provide handlers/middlewares for app routes.
 * ---------------------------------------------------------------------------------------------------------------------
 *  Note that you can move this file content to the "../../index.php" file, but it's recommend to use this file to
 *  allow other SAPIs to make use of it (the routes registration part).
 * ---------------------------------------------------------------------------------------------------------------------
 *  You can freely add additional files in the '/includes/routes/' directory. VELOX will load it for you.
 * ---------------------------------------------------------------------------------------------------------------------
 */

use MAKS\Velox\Backend\Router;

Router::handle('/', function () {
    return View::render('home', ['title' => 'Home']);
});



Router::middleware('/contact', function () {
    $message = Globals::getPost('message');
    if ($message) {
        return hse($message, ENT_QUOTES);
    }
}, 'POST');

Router::handle('/contact', function ($path, $match, $previous) {
    return View::render('contact', [
        'title' => 'Contact',
        'message' => $previous ?? null
    ]);
}, ['GET', 'POST']);



$controller = new \App\Controller\DefaultController();
Router::handle('/example', [$controller, 'exampleAction']);



Router::handle('/redirect', function () {
    // this will take place in client's browser (client knows about it).
    Router::redirect('/');
});

Router::handle('/forward', function () {
    // this will take place in the application (client does not know about it).
    Router::forward('/');
});



// this will match anything after the slash
Router::any('/dump/{var?}', function ($path, $match, $previous) {
    return View::render('dump', ['vars' => compact('path', 'match', 'previous')], 'simple');
});
// this will match numbers only
Router::get('/number/([0-9]+)', function ($path, $match) {
    Router::forward('/dump/' . $match);
});
// this will match letters only
Router::get('/string/([a-z]+)', function ($path, $match) {
    Router::forward('/dump/' . $match);
});



Router::handle('/development-exception', function () {
    Config::set('global.env', 'DEVELOPMENT');

    throw new \Exception('Test!');
});

Router::handle('/production-exception', function () {
    Config::set('global.env', 'PRODUCTION');
    Config::set('global.errorPage', null);

    throw new \Exception('Test!');
});



// '/not-found'   -> will be forwarded to Router::handleRouteNotFound()
// '/not-allowed' -> will be forwarded to Router::handleMethodNotAllowed() (if request method is not POST)
Router::post('/not-allowed', fn () => '');



Router::handleRouteNotFound(function ($path) {
    return View::render('error/404', compact('path'));
});

Router::handleMethodNotAllowed(function ($path, $method) {
    return View::render('error/405', compact('path', 'method'));
});



// registers all pages configured in "./config/data.php"
foreach ((array)Config::get('data.pages') as $page) {
    Router::handle($page['route'], function () use ($page) {
        return View::render($page['page'], $page['variables'], $page['layout']);
    }, $page['method']);
}
