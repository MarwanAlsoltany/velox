<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 *  This file can be used to provide listeners for system events (events provided by VELOX classes).
 * ---------------------------------------------------------------------------------------------------------------------
 *  Note that you can in theory register listeners for these events anywhere in the project, but it's recommended to
 *  register them here to prepare listeners/bindings before the events are dispatched/triggered.
 * ---------------------------------------------------------------------------------------------------------------------
 *  You can freely add additional files in the '/includes/events/' directory. VELOX will load it for you.
 * ---------------------------------------------------------------------------------------------------------------------
 */



// Config::class
// -------------
// * 'config.on.load': This event will be dispatched when the config is loaded. It will be passed a reference to the config array.
// * 'config.on.cache': This event will be dispatched when the config is cached. It will not be passed any arguments.
// * 'config.on.clearCache': This event will be dispatched when the config cache is cleared. It will not be passed any arguments.
// -------------

// Controller::class
// -------------
// * 'controller.on.construct': This event will be dispatched when a the controller (or a subclass) is constructed. It will not be passed any arguments, but will be bound to the object.
// -------------

// Router::class
// -------------
// * 'router.on.registerHandler': This event will be dispatched when a handler is registered. It will be passed a reference to route config array.
// * 'router.on.registerMiddleware': This event will be dispatched when a middleware is registered. It will be passed a reference to route config array.
// * 'router.on.start': This event will be dispatched when the router is started. It will be passed a reference to the current parameters.
// * 'router.before.redirect': This event will be dispatched when a redirect is attempted. It will be passed the redirection path/url.
// * 'router.before.forward': This event will be dispatched when a forward is attempted. It will be passed the forward path.
// -------------

// Data::class
// -----------
// * 'data.on.load': This event will be dispatched when the data is loaded. It will be passed a reference to the data array.
// -----------

// View::class
// -----------
// * 'view.before.render': This event will be dispatched when a view rendering is attempted. It will be passed a reference to the array that will be passed to the view as variables.
// * 'view.on.cache': This event will be dispatched when a view is cached. It will not be passed any arguments.
// * 'view.on.cacheClear': This event will be dispatched when a view cache is cleared. It will not be passed any arguments.
// -----------



// Examples
// --------
// * Checkout "/storage/logs/events.log" to see the result.
// --------

Event::listen('config.on.load', function (&$config) {
    App::log('The config was loaded', null, 'events');
});

Event::listen('controller.on.construct', function () {
    App::log('The {class} has been constructed', ['class' => get_class($this)], 'events');
});

Event::listen('router.on.registerHandler', function (&$route) {
    App::log('The handler for the route "{route}" was registered', ['route' => $route['expression']], 'events');
});

Event::listen('data.on.load', function (&$data) {
    App::log('The data was loaded', null, 'events');
});

Event::listen('view.before.render', function (&$variables) {
    $variables['__uid'] = uniqid();
    App::log('The UID "{uid}" was added to the view as "$__uid"', ['uid' => $variables['__uid']], 'events');
});
