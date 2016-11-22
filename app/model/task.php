<?php

namespace Model;

use Util\MySql;

class Task extends Root
{
    /**
     * @var int
     */
    protected $createdTimestamp;

    /**
     * @var int
     */
    protected $updatedTimestamp;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $instructions;

    /**
     * @var int
     */
    protected $createdByUserId;

    /**
     * @var string
     */
    protected $requirements;

    /**
     * @var int
     */
    protected $deadlineTimestamp;

    /**
     * @return int
     */
    public function getCreatedTimestamp()
    {
        return $this->createdTimestamp;
    }

    /**
     * @param int $createdTimestamp
     * @return $this
     */
    public function setCreatedTimestamp($createdTimestamp)
    {
        $this->createdTimestamp = $createdTimestamp;
        return $this;
    }

    /**
     * @return int
     */
    public function getUpdatedTimestamp()
    {
        return $this->updatedTimestamp;
    }

    /**
     * @param int $updatedTimestamp
     * @return $this
     */
    public function setUpdatedTimestamp($updatedTimestamp)
    {
        $this->updatedTimestamp = $updatedTimestamp;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * @param string $instructions
     * @return $this
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;
        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedByUser()
    {
        return User::getById($this->createdByUserId);
    }

    /**
     * @param User $createdByUser
     * @return $this
     */
    public function setCreatedByUser($createdByUser)
    {
        if ($createdByUser) {
            $this->createdByUserId = $createdByUser->getId();
        }
        else {
            $this->createdByUserId = null;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @param string $requirements
     * @return $this
     */
    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeadlineTimestamp()
    {
        return $this->deadlineTimestamp;
    }

    /**
     * @param int $deadlineTimestamp
     * @return $this
     */
    public function setDeadlineTimestamp($deadlineTimestamp)
    {
        $this->deadlineTimestamp = $deadlineTimestamp;
        return $this;
    }

    /**
     * @param User $user
     * @return Task|null
     */
    public static function getForUser($user)
    {

    }

    /**
     * @return Task|null
     */
    public static function getOpen()
    {

    }

    /**
     * @param string $tag
     * @return Task|null
     */
    public static function getOpenForTag($tag)
    {

    }

    /**
     * @param int $id
     * @return Task|null
     */
    public static function getById($id)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `Task` WHERE `id` = :id LIMIT 1',
            array(':id'=>$id));

        return self::populateOne($result);
    }

    /**
     * @param array $result
     * @return Task
     */
    protected static function populateOne($result)
    {
        if (!$result) {
            return null;
        }

        $obj = new self;

        $obj->setId($result[0]['id'])
            ->setCreatedTimestamp($result[0]['createdTimestamp'])
            ->setUpdatedTimestamp($result[0]['updatedTimestamp'])
            ->setDeleted($result[0]['deleted']);

        return $obj;
    }

    /**
     * @return $this
     */
    public function update()
    {
        $mysql = MySql::instance();

        $this->updatedTimestamp = time();

        $result = $mysql->query(
            'UPDATE `Task` SET `deleted` = :deleted, `createdTimestamp` = :createdTimestamp, ' .
            '`updatedTimestamp` = :updatedTimestamp, WHERE `id` = :id',
            array(
                ':deleted'=>$this->deleted,
                ':createdTimestamp'=>$this->createdTimestamp,
                ':updatedTimestamp'=>$this->updatedTimestamp,
                ':id'=>$this->id
            )
        );

        return $this;
    }

    /**
     * @return Task
     */
    public static function create()
    {
        $obj = new self;

        $now = time();

        $obj->setCreatedTimestamp($now)
            ->setUpdatedTimestamp($now)
            ->insert();

        return $obj;
    }

    protected function insert()
    {
        $mysql = MySql::instance();

        $result = $mysql->query(array(
            'INSERT INTO `Task` (`deleted`, `createdTimestamp`, `updatedTimestamp`,) ' .
            'VALUES (0, :createdTimestamp, :updatedTimestamp, )',
            'SELECT LAST_INSERT_ID() AS id'
        ),
            array(
                array(
                    ':createdTimestamp'=>$this->createdTimestamp,
                    ':updatedTimestamp'=>$this->updatedTimestamp,
                ),
                array()
            )
        );

        $this->id = $result[0]['id'];
    }
}