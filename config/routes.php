<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::plugin(
    'SpongeUsers',
    ['path' => '/sponge-users'],
    function (RouteBuilder $routes) {
        $routes->connect('/', ['controller' => 'SpongeUsers', 'action' => 'index']);
        $routes->fallbacks(DashedRoute::class);
    }
);
