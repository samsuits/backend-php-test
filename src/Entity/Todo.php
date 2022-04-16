<?php

namespace Entity;

/**
 * Todo
 *
 * @Table(name="todos")
 * @Entity()
 */
class Todo
{
    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Many todos have one user. This is the owning side.
     * @ManyToOne(targetEntity="Entity\User", inversedBy="todos")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @Column(name="completed", type="integer", length=1, nullable=true)
     */
    private $completed;

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
     * Set description
     *
     * @param string $description
     *
     * @return Todo
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set completed
     *
     * @param integer $completed
     *
     * @return Todo
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get completed
     *
     * @return integer
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Set user
     *
     * @param \Entity\User $user
     *
     * @return Todo
     */
    public function setUser(\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get User Id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->getUser()->getId();
    }

    /**
     * To Array
     */
    public function toArray()
    {
       return [
            "id" => $this->id,
           "user_id" => $this->getUser()->getId(),
           "description" => $this->description,
           "completed" => $this->completed
        ];
    }
}
