<?php

final class  DoctrineORMTest extends \PHPUnit\Framework\TestCase
{
    private $app;

    public function setUp()
    {
        $this->app = require __DIR__.'/../src/app.php';
    }

    public function testCanAccessEntityManager()
    {
        $em = $this->app['orm.em'];
        $this->assertTrue(!empty($this->app['orm.em']));
        $this->assertInstanceOf(Doctrine\ORM\EntityManager::class, $em);
    }

}