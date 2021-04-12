<?php

require_once 'bootstrap/autoload.php';



Router::handle('/', function () {
    return View::render('home', ['title' => 'Home']);
});



Router::middleware('/contact', function () {
    if (isset($_POST['message']) && !empty($_POST['message'])) {
        return htmlspecialchars($_POST['message'], ENT_QUOTES);
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



Router::post('/not-allowed', function () {
    // '/not-allowed' will be forwarded to Router::handleMethodNotAllowed()
    // when visited via browser (if request method is not POST)

    // '/not-found' will be forwarded to Router::handleRouteNotFound()
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



Router::handle('/redirect', function () {
    // this will take place in client's browser (client knows about it).
    Router::redirect('/');
});

Router::handle('/forward', function () {
    // this will take place in the application (client does not know about it).
    Router::forward('/');
});



// this will match anything after the slash
// Router::get('/dump/{var?}', 'dd');
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



Router::handleRouteNotFound(function ($path) {
    return View::render('error/404', compact('path'));
});

Router::handleMethodNotAllowed(function ($path, $method) {
    return View::render('error/405', compact('path', 'method'));
});



// registers all pages configured in "./config/data.php"
foreach ((array)Data::get('pages') as $page) {
    Router::handle($page['route'], function ($path, $match, $previous) use ($page) {
        return View::render(
            $page['page'],
            $page['variables'],
            $page['layout']
        );
    }, $page['method']);
}



Router::start();
