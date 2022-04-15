<?php

use Kosinix\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        echo $sql;
        $user = $app['db']->fetchAssoc($sql);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}/{format}', function ($id, $format) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        if ($format && $format == 'json') {
            return $app->json($todo);
        }

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        return $app->redirect('/todolist');
    }
})
->value('id', null)
    ->value('format', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    if (empty(trim($description))) {
        $app['session']->getFlashBag()->add('errors', 'Description cannot be empty.');
        return $app->redirect('/todo');
    }

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('messages', 'Todo successfully added');

    return $app->redirect('/todo');
});

$app->match('/todo/complete/{id}', function ($id) use ($app) {

    $sql = "UPDATE todos SET completed = 1 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('messages', 'Todo #' . $id. ' marked as completed');

    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);
    $app['session']->getFlashBag()->add('messages', 'Todo successfully deleted');

    return $app->redirect('/todo');
});

$app->get('/todolist/{page}/{sort_by}/{sorting}', function ($page, $sort_by, $sorting) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = 'SELECT COUNT(*) AS `total` FROM todos';
    $count = $app['db']->fetchAssoc($sql);
    $count = (int) $count['total'];


    /** @var \Kosinix\Paginator $paginator */
    $paginator =  $app['paginator']($count, $page);

    $sql = sprintf('SELECT * FROM todos ORDER BY %s %s LIMIT %d,%d',
        $sort_by, strtoupper($sorting), $paginator->getStartIndex(), $paginator->getPerPage());

    $todos = $app['db']->fetchAll($sql);

    $pagination = new Pagination($paginator, $app['url_generator'], 'todolist', $sort_by, $sorting);

    return $app['twig']->render('todos.html', [
        'todos' => $todos,
        'pagination' => $pagination
    ]);
})->value('page', 1)
    ->value('sort_by', 'id')
    ->value('sorting', 'asc')
    ->assert('page', '\d+') // Numbers only
    ->assert('sort_by','[a-zA-Z_]+') // Match a-z, A-Z, and "_"
    ->assert('sorting','(\basc\b)|(\bdesc\b)') // Match "asc" or "desc"
    ->bind('todolist');