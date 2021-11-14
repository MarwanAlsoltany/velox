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



// Examples
// --------
// * Check out "/storage/logs/events.log" to see the result.

Event::listen(\MAKS\Velox\Backend\Config::ON_LOAD, function (&$config) {
    App::log('The config was loaded', null, 'events');

    Config::set('eventExecuted', true);
    if ($config['eventExecuted']) {
        App::log('The config was manipulated', null, 'events');
    }
});

Event::listen(\MAKS\Velox\Backend\Controller::ON_CONSTRUCT, function () {
    /** @var \MAKS\Velox\Backend\Controller $this */
    $this->vars['__uid'] = uniqid();

    App::log('The "{class}" has been constructed', ['class' => get_class($this)], 'events');
});

Event::listen(\MAKS\Velox\Backend\Router::ON_REGISTER_HANDLER, function (&$route) {
    App::log('The handler for the route "{route}" has been registered', ['route' => $route['expression']], 'events');
});

Event::listen(\MAKS\Velox\Frontend\Data::ON_LOAD, function (&$data) {
    App::log('The data was loaded', null, 'events');

    Data::set('eventExecuted', true);
    if ($data['eventExecuted']) {
        App::log('The data was manipulated', null, 'events');
    }
});

Event::listen(\MAKS\Velox\Frontend\View::BEFORE_RENDER, function (&$variables) {
    $variables['__uid'] = uniqid();
    App::log('The UID "{uid}" was added to the view as "$__uid"', ['uid' => $variables['__uid']], 'events');
});



// Available events
// ----------------
// * Config::ON_LOAD
// * Config::ON_CACHE
// * Config::ON_CLEAR_CACHE
// * Controller::ON_CONSTRUCT
// * Router::ON_REGISTER_HANDLER
// * Router::ON_REGISTER_MIDDLEWARE
// * Router::ON_START
// * Router::BEFORE_REDIRECT
// * Router::BEFORE_FORWARD
// * Data::ON_LOAD
// * View::BEFORE_RENDER
// * View::ON_CACHE
// * View::ON_CACHE_CLEAR
