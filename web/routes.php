<?php

    function routes() {
        $routes = new Routes();

        $routes->get('/', 'MainController@index');
        $routes->get('/contact', 'MainController@contact');
        $routes->get('/ourDog', 'MainController@ourDog');
        $routes->get('/news', 'MainController@news');
        $routes->get('/links', 'MainController@links');
        $routes->get('/impressum', 'MainController@impressum');
        $routes->get('/privacy', 'MainController@privacy');
        $routes->get('/dogs/information', 'MainController@information');
        $routes->get('/dogs/breeds/a', 'MainController@breeds');

        $routes->get('/login', 'AuthController@login');
        $routes->post('/login', 'AuthController@login');
        $routes->get('/register', 'AuthController@register');
        $routes->post('/register', 'AuthController@register');

        $routes->get('/admin', 'AdminController@index');

        $routes->get('/admin/news', 'NewsController@index');
        $routes->both('/admin/news/create', 'NewsController@create');
        $routes->both('/admin/news/edit', 'NewsController@edit');
        $routes->get('/admin/news/delete', 'NewsController@delete');

        $routes->get('/admin/ourDog', 'DiashowController@index');
        $routes->both('/admin/ourDog/create', 'DiashowController@create');
        $routes->both('/admin/ourDog/update', 'DiashowController@update');
        $routes->get('/admin/ourDog/delete', 'DiashowController@delete');

        return $routes;
    }
?>
