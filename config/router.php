<?php

/**
 * This config file can be used to override "Router::class" default parameters.
 *
 * @see \MAKS\Velox\Backend\Router
 */



return [


    // The base URL of the application.
    'base'            => Router::DEFAULTS['base'],


    // Whether to allow for multiple route matching or not.
    'allowMultiMatch' => Router::DEFAULTS['allowMultiMatch'],


    // Whether route matching should be case sensitive or not.
    'caseMatters'     => Router::DEFAULTS['caseMatters'],


    // Whether trailing slashes in the route matter or not.
    'slashMatters'    => Router::DEFAULTS['slashMatters'],


    // Whether to start the router automatically without the need for calling `Router::start()` or not.
    'allowAutoStart'  => Router::DEFAULTS['allowAutoStart'],


];
