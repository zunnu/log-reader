<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::plugin(
    'LogReader',
    ['path' => '/log-reader'],
    function (RouteBuilder $routes) {
        $routes->connect('/', ['controller' => 'LogReader', 'action' => 'index'])->setExtensions(['json']);
        $routes->fallbacks(DashedRoute::class);
    }
);
