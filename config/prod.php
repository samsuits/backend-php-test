<?php

// configure your app for the production environment

$app['twig.path'] = array(__DIR__.'/../templates');

// Doctrine (db)
$app['db.options'] = array(
    'driver' => 'pdo_mysql',
    'charset' => 'utf8',
    'host' => 'localhost',
    'dbname' => 'ac_todos',
    'user' => 'root',
    'password' => ''
);

// Doctrine (ORM)
$app['orm.proxies_dir'] = './resources/doctrine/proxies';
$app['orm.default_cache'] = array(
    'driver' => 'filesystem',
    'path' => __DIR__ . 'doctrine/cache'
);
$app['orm.em.options'] = array(
    'mappings' => array(
        array(
            'type' => 'annotation',
            'path' => __DIR__ . '/../../src',
            'namespace' => 'Entity'
        )
    ),
);