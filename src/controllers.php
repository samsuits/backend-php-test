<?php

use Kosinix\Pagination;
use Symfony\Component\HttpFoundation\Request;

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
    // Redirect to todo if already login
    if ($app['session']->get('user')) {
        return $app->redirect('/todo');
    }
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        // Entity Manager
        $em = $app['orm.em'];

        // User Repository
        $userRepo = $em->getRepository('Entity\User');

        $user = $userRepo->findOneBy(array('username' => $username));

        // Verifying user and password
        if ($user && password_verify($password, $user->getPassword())) {
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
        $app['session']->getFlashBag()->add('errors', 'Invalid username or password');
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

    if ($id) {
        // Entity Manager
        $em = $app['orm.em'];

        // Query Builder
        $todoQb = $em
            ->getRepository('Entity\Todo')
            ->createQueryBuilder('t');

        $query = $todoQb
            ->join('t.user', 'u')
            ->where('t.id = :tid')
            ->andWhere('u.id = :uid')
            ->setParameter('tid', $id)
            ->setParameter('uid', $user->getId())
            ->getQuery();

        $todo = $query->setMaxResults(1)->getOneOrNullResult();

        if (empty($todo)) {
            $app['session']->getFlashBag()->add('errors', 'Todo cannot be found.');
            return $app->redirect('/todo');
        }

        if ($format && $format == 'json') {
            return $app->json($todo->toArray());
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

    $description = $request->get('description');

    if (empty(trim($description))) {
        $app['session']->getFlashBag()->add('errors', 'Description cannot be empty.');
        return $app->redirect('/todo');
    }

    // Entity Manager
    $em = $app['orm.em'];

    $todo = new \Entity\Todo();
    $todo->setDescription($description);
    $todo->setUser($user);

    // Save todo to database
    $em->merge($todo);
    $em->flush();

    $app['session']->getFlashBag()->add('messages', 'Todo successfully added');

    return $app->redirect('/todo');
});

$app->match('/todo/complete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // return if id is empty
    if(empty($id)) {
        $app['session']->getFlashBag()->add('errors', 'Todo Id required');
        return $app->redirect('/todo');
    }

    // Entity Manager
    $em = $app['orm.em'];

    // Todo Repository
    $todoRepo = $em->getRepository('Entity\Todo');

    $todo = $todoRepo->findOneById($id);

    if(empty($todo)) {
        $app['session']->getFlashBag()->add('errors', 'Cannot find todo');
        return $app->redirect('/todo');
    }

    $todo->setCompleted(1);

    // Update database
    $em->persist($todo);
    $em->flush();

    $app['session']->getFlashBag()->add('messages', 'Todo #' . $id. ' marked as completed');

    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // return if id is empty
    if(empty($id)) {
        $app['session']->getFlashBag()->add('errors', 'Todo Id required');
        return $app->redirect('/todo');
    }

    // Entity Manager
    $em = $app['orm.em'];

    // Todo Repository
    $todoRepo = $em->getRepository('Entity\Todo');

    $todo = $todoRepo->findOneById($id);

    if(empty($todo)) {
        $app['session']->getFlashBag()->add('errors', 'Cannot find todo');
        return $app->redirect('/todo');
    }

    // Remove from database
    $em->remove($todo);
    $em->flush();

    $app['session']->getFlashBag()->add('messages', 'Todo successfully deleted');

    return $app->redirect('/todo');
});

$app->get('/todolist/{page}', function ($page) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $em = $app['orm.em'];
    $repo = $em
        ->getRepository('Entity\Todo');

    $count = $repo
        ->createQueryBuilder('t')
        ->select('count(t.id)')
        ->join('t.user', 'u')
        ->where('u.id = :uid')
        ->setParameter('uid', $user->getId())
        ->getQuery()
        ->getSingleScalarResult();


    /** @var \Kosinix\Paginator $paginator */
    $paginator =  $app['paginator']($count, $page);

    $todoQb = $repo
        ->createQueryBuilder('t')
        ->join('t.user', 'u')
        ->where('u.id = :uid')
        ->setParameter('uid', $user->getId())
        ->setFirstResult($paginator->getStartIndex())
        ->setMaxResults($paginator->getPerPage());

    $todos = $todoQb->getQuery()->getResult();

    $pagination = new Pagination($paginator, $app['url_generator'], 'todolist', 'id', 'asc');

    return $app['twig']->render('todos.html', [
        'todos' => $todos,
        'pagination' => $pagination
    ]);
})->value('page', 1)
    ->assert('page', '\d+') // Numbers only
    ->bind('todolist');