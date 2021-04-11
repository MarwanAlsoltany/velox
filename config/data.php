<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 *  This config file can be used to provide some additional reuseable data that will be injected in the "Data::class".
 *  This data gets moved from the "Config::class" to the "Data::class" after the first call to some method on it.
 *  This file can be replaced with a directory with the same name and all files inside it will be loaded recursively.
 * ---------------------------------------------------------------------------------------------------------------------
 */



return [


    'pages' => [
        // # example (extend or change the definition to your liking)
        // [
        //     'route'     => '/',
        //     'method'    => 'GET',
        //     'page'      => 'home',
        //     'layout'    => 'base',
        //     'variables' => [
        //         'title' => 'Home',
        //         // ...
        //     ],
        // ],

        // # add more pages ...
    ],


    'menus' => [
        // # example (extend or change the definition to your liking)
        // 'main' => [
        //     [
        //         'name'  => 'Home',
        //         'route' => '/',
        //         'title' => 'Homepage'
        //     ]
        // ]

        // # add more menus ...
    ]


];
