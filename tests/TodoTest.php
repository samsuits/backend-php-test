<?php

namespace Tests;

use Entity\Todo;

final class TodoTest extends \PHPUnit\Framework\TestCase
{
    private $app;

    public function setUp()
    {
        $this->app = require __DIR__ . '/../src/app.php';
    }

    /**
     * Test find Todo By Id
     *
     * @return void
     */
    public function testFindTodoById()
    {
        $toDoId = 1;
        $em = $this->app['orm.em'];

        $repo = $em->getRepository(Todo::class);

        $todo = $repo->findById($toDoId);

        if ($todo) {
            $this->assertEquals($toDoId, $todo->getId());
        }

    }
}