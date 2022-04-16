<?php

namespace Entity;

/**
 * User
 *
 * @Table(name="users")
 * @Entity
 */

class User
{
    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Column(name="username", type="string", length=255, nullable=false)
     */
    private $username;

    /**
     * @Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * One user has many todos. This is the inverse side
     * @OneToMany(targetEntity="Entity\Todo", mappedBy="user")
     */
    private $todos;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->todos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Add todo
     *
     * @param \Entity\Todo $todo
     *
     * @return User
     */
    public function addTodo(\Entity\Todo $todo)
    {
        $this->todos[] = $todo;

        return $this;
    }

    /**
     * Remove todo
     *
     * @param \Entity\Todo $todo
     */
    public function removeTodo(\Entity\Todo $todo)
    {
        $this->todos->removeElement($todo);
    }

    /**
     * Get todos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTodos()
    {
        return $this->todos;
    }
}
