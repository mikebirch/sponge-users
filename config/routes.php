<?php
use Cake\Routing\Route\DashedRoute;

$routes->plugin(
    'SpongeUsers',
    ['path' => '/sponge-users'],
    function ($routes) {
        $routes->setRouteClass(DashedRoute::class);
        $routes->get('/', ['controller' => 'SpongeUsers', 'action' => 'index']);
    }
);
