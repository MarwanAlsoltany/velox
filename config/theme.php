<?php

/**
 * This config file can be used to edit/extend theme configuration.
 *
 * This file is mainly used by the "View::class" and the "Path::class".
 *
 * @see \MAKS\Velox\Frontend\View
 * @see \MAKS\Velox\Frontend\Path
 */



return [


    // Currently active theme.
    'active' => 'velox',


    // Theme parent(s), array or string. Used to inherit view files from.
    'parent' => 'default',


    // Theme directory structure.
    'paths'  => [
        'root'     => '{global.paths.themes}/{theme.active}',
        'assets'   => '{theme.paths.root}/assets',
        'layouts'  => '{theme.paths.root}/layouts',
        'pages'    => '{theme.paths.root}/pages',
        'partials' => '{theme.paths.root}/partials',
    ],


];
