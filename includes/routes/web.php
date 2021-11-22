<?php

/**
 * This file can be used to provide handlers/middlewares for app routes.
 *
 * Note that you can move this file content to the "../../index.php" file, but it's recommend to use this file to
 * allow other SAPIs to make use of it (the routes registration part).
 *
 * You can freely add additional files in the '/includes/routes/' directory. VELOX will load it for you.
 */



Router::handle('/', function () {
    return View::render('home', ['title' => 'Home']);
});



Router::middleware('/contact', function () {
    $message = Globals::getPost('message');
    if ($message) {
        return htmlspecialchars($message, ENT_QUOTES);
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

// the routes in the following controllers will be registered automatically
// as it's using annotation to define routes, they just needs to be
// instantiated once for the auto-registration to take effect.
new \App\Controller\UsersController();
new \App\Controller\PersonsController();



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



// The /development-exception and /production-exception routes are for demonstration purposes only.

Router::handle('/development-exception', function () {
    Config::set('global.env', 'DEVELOPMENT');

    throw new \Exception('Test!');
});

Router::handle('/production-exception', function () {
    Config::set('global.env', 'PRODUCTION');
    Config::set('global.errorPages.500', null); // skip configured 500 error page

    throw new \Exception('Test!');
});



// The /401, /403, /404, /405, and /500 routes are for demonstration purposes only.

Router::get('/401', function () {
    Auth::fail();
});

Router::get('/403', function () {
    // mimicking a 403 error,
    // this will render "{global.errorPages.403}" from config
    Config::set('session.csrf.methods', ['GET']); // making CSRF checking for GET requests only

    Session::csrf()->token(); // generate a new CSRF token
    Session::csrf()->check(); // check the CSRF token
});

Router::get('/404-' . uniqid(), function () {
    // if requested path is not 404-XXXXXXXXXXXXX,
    // request will be forwarded to Router::handleRouteNotFound()
    // or fall back to render "{global.errorPages.404}"
    return '';
});

Router::post('/405', function () {
    // if request method is not POST,
    // request will be forwarded to Router::handleMethodNotAllowed()
    // or fall back to render "{global.errorPages.403}" from config
    return '';
});

Router::get('/500', function () {
    // mimicking a 500 error,
    // this will render "{global.errorPages.500}" from config
    Config::set('global.env', 'PRODUCTION');

    throw new \Exception('Test!');
});



// registers all pages configured in "./config/data.php"
foreach ((array)Config::get('data.pages') as $page) {
    Router::handle($page['route'], function () use ($page) {
        return View::render($page['page'], $page['variables'], $page['layout']);
    }, $page['method']);
}
