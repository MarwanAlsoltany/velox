<?php

/**
 * This config file can be used to customize everything about the views. It is used by the "View::class".
 *
 * @see \MAKS\Velox\Frontend\View
 */



return [


    // The file extension used when looking for view files (Layouts, Pages, Partials).
    'fileExtension'      => View::DEFAULTS['fileExtension'],


    // Whether to inherit view files from parent theme.
    'inherit'            => View::DEFAULTS['inherit'],


    // Whether to minify the rendered views.
    'minify'             => View::DEFAULTS['minify'],


    // Whether to cache the rendered views as static HTML files.
    'cache'              => View::DEFAULTS['cache'],
    // An array or string of pages that should never be cached (useful for dynamic pages).
    'cacheExclude'       => View::DEFAULTS['cacheExclude'],
    // Whether to cache the views in nested directories as "index.html" or as file with an autogenerated name based on its path.
    'cacheAsIndex'       => View::DEFAULTS['cacheAsIndex'],
    // Whether to put a comment with a timestamp in the cached view file.
    'cacheWithTimestamp' => View::DEFAULTS['cacheWithTimestamp'],


    // The default names of files VELOX falls back to when rendering a view.
    'defaultLayoutName'  => 'main', // View::DEFAULTS['name'],
    'defaultPageName'    => View::DEFAULTS['name'],
    'defaultPartialName' => View::DEFAULTS['name'],
    'defaultSectionName' => View::DEFAULTS['name'],


    // The variables passed to all Layouts, Pages, or Partials by default, they will be passed as an array under "default*Vars".
    'defaultLayoutVars'  => View::DEFAULTS['variables'],
    'defaultPageVars'    => View::DEFAULTS['variables'],
    'defaultPartialVars' => View::DEFAULTS['variables'],


    // Templating engine configuration.
    'engine'             =>  View::DEFAULTS['engine'],


];
