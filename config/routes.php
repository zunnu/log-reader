<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::plugin(
    'LogReader',
    ['path' => '/log-reader'],
    function (RouteBuilder $routes) {
        $routes->connect('/', ['controller' => 'LogReader', 'action' => 'index'])->setExtensions(['json']);

        $routes->prefix('api', function (RouteBuilder $routes) {
            $routes->prefix('v1', function (RouteBuilder $routes) {
                $routes->connect('/files', ['controller' => 'LogReader', 'action' => 'files'])->setExtensions(['json']);
                $routes->connect('/types', ['controller' => 'LogReader', 'action' => 'types'])->setExtensions(['json']);
                $routes->connect('/logs', ['controller' => 'LogReader', 'action' => 'logs'])->setExtensions(['json']);
            //     // $routes->connect('/:controller');
            });
        });
        $routes->fallbacks(DashedRoute::class);
    }
);
