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
     * @var bool
     */
    protected $published = false;

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * @param bool $published
     * @return $this
     */
    public function setPublished($published)
    {
        $this->published = $published;
        return $this;
    }

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
     * @param int $userId
     * @return $this
     */
    protected function setCreatedByUserId($userId)
    {
        $this->createdByUserId = $userId;
        return $this;
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
     * @return bool
     */
    public function hasDeadlinePassed()
    {
        return time() >= $this->deadlineTimestamp;
    }

    /**
     * @return bool|string
     */
    public function getFormattedDeadline()
    {
        return date("D, F j Y, H:i:s", $this->deadlineTimestamp);
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
    public static function getCreatedForUser($user)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `Task` WHERE `createdByUserId` = :userId AND `deleted` = 0',
            array(':userId'=>$user->getId()));

        return self::populateMany($result);
    }

    /**
     * @param User $user
     * @return Task|null
     */
    public static function getCreatedAndOpenForUser($user)
    {
        $mysql = MySql::instance();

        $now = time();
        $result = $mysql->query('SELECT * FROM `Task` WHERE `createdByUserId` = :userId AND `published` = 1 ' .
                                'AND `deadlineTimestamp` > :now AND `deleted` = 0',
            array(
                ':userId'=>$user->getId(),
                ':now'=>$now
            )
        );

        return self::populateMany($result);
    }

    /**
     * get all without a passed deadline and published
     *
     * @return Task[]|null
     */
    public static function getOpen()
    {
        $mysql = MySql::instance();

        $now = time();

        $result = $mysql->query('SELECT * FROM `Task` WHERE `deadlineTimestamp` > :now AND `published` = 1 AND `deleted` = 0',
            array(':now'=>$now));

        return self::populateMany($result);
    }

    /**
     * get all without a passed deadline and published, by tag
     *
     * @param string $tag
     * @return Task[]|null
     */
    public static function getOpenForTag($tag)
    {
        // we could do this with a join query, but since we have the objects...
        $taskTags = TaskTag::getAllForTag($tag);

        if (!$taskTags) {
            return null;
        }

        $tasks = [];

        foreach ($taskTags as $taskTag) {
            $task = $taskTag->getTask();

            if ($task) {
                $tasks[] = $task;
            }
        }

        return $tasks;
    }

    /**
     * @param int $id
     * @return Task|null
     */
    public static function getById($id)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `Task` WHERE `id` = :id AND `deleted` = 0 LIMIT 1',
            array(':id'=>$id));

        return self::populateOne($result[0]);
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

        $obj->setId($result['id'])
            ->setCreatedTimestamp($result['createdTimestamp'])
            ->setUpdatedTimestamp($result['updatedTimestamp'])
            ->setDeleted($result['deleted'])
            ->setCreatedByUserId($result['createdByUserId'])
            ->setTitle($result['title'])
            ->setInstructions($result['instructions'])
            ->setRequirements($result['requirements'])
            ->setDeadlineTimestamp($result['deadlineTimestamp'])
            ->setPublished($result['published']);

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
            'UPDATE `Task` SET `deleted` = :deleted, `createdTimestamp` = :createdTimestamp, `updatedTimestamp` = :updatedTimestamp, ' .
            '`createdByUserId` = :createdByUserId, `title` = :title, `instructions` = :instructions, `requirements` = :requirements, ' .
            '`deadlineTimestamp` = :deadlineTimestamp, `published` = :published ' .
            'WHERE `id` = :id',
            array(
                ':deleted'=>$this->deleted,
                ':createdTimestamp'=>$this->createdTimestamp,
                ':updatedTimestamp'=>$this->updatedTimestamp,
                ':createdByUserId'=>$this->createdByUserId,
                ':title'=>$this->title,
                ':instructions'=>$this->instructions,
                ':requirements'=>$this->requirements,
                ':deadlineTimestamp'=>$this->deadlineTimestamp,
                ':published'=>$this->published,
                ':id'=>$this->id
            )
        );

        return $this;
    }

    /**
     * Don't need much for insert, we'll get the rest later
     *
     * @param string $title
     * @param string $instructions
     * @param User $createdByUser
     * @return Task
     */
    public static function create($title, $instructions, $createdByUser)
    {
        $obj = new self;

        $now = time();

        $obj->setCreatedTimestamp($now)
            ->setUpdatedTimestamp($now)
            ->setCreatedByUser($createdByUser)
            ->setTitle($title)
            ->setInstructions($instructions)
            ->insert();

        return $obj;
    }

    protected function insert()
    {
        $mysql = MySql::instance();

        $result = $mysql->query(array(
            'INSERT INTO `Task` (`deleted`, `createdTimestamp`, `updatedTimestamp`, `title`, `createdByUserId`, `published`, `instructions`) ' .
            'VALUES (0, :createdTimestamp, :updatedTimestamp, :title, :createdByUserId, 0, :instructions)',
            'SELECT LAST_INSERT_ID() AS id'
        ),
            array(
                array(
                    ':createdTimestamp'=>$this->createdTimestamp,
                    ':updatedTimestamp'=>$this->updatedTimestamp,
                    ':title'=>$this->title,
                    ':instructions'=>$this->instructions,
                    ':createdByUserId'=>$this->createdByUserId
                ),
                array()
            )
        );

        $this->id = $result[0]['id'];
    }
}