<?php

namespace Model;


use Util\MySql;

class TaskTag extends Root
{
    /**
     * @var string
     */
    protected $tag;

    /**
     * @var int
     */
    protected $taskId;

    /**
     * @throws \Exception
     */
    public function isDeleted()
    {
        throw new \Exception('isDeleted() not supported on TaskTag');
    }

    /**
     * @throws \Exception
     */
    public function getId()
    {
        throw new \Exception('getId() not supported on TaskTag');
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     * @return $this
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return Task|null
     */
    public function getTask()
    {
        return Task::getById($this->taskId);
    }

    /**
     * @param int $taskId
     * @return $this
     */
    protected function setTaskId($taskId)
    {
        $this->taskId = $taskId;
        return $this;
    }

    /**
     * @param Task $task
     * @return $this
     */
    public function setTask($task)
    {
        if ($task) {
            $this->taskId = $task->getId();
        }
        else {
            $this->taskId = null;
        }

        return $this;
    }

    /**
     * @param string $tag
     * @return TaskTag[]|null
     */
    public static function getAllForTag($tag)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `TaskTag` WHERE `tag` = :tag',
                                array(':tag'=>$tag));

        return self::populateMany($result);
    }

    /**
     * @param Task $task
     * @return TaskTag[]
     */
    public static function getAllForTask($task)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `TaskTag` WHERE `taskId` = :taskId',
            array(':taskId'=>$task->getId()));

        return self::populateMany($result);
    }

    /**
     * @param array $result
     * @return TaskTag|null
     */
    protected static function populateOne($result)
    {
        if (!$result) {
            return null;
        }

        $obj = new self;

        $obj->setId(null)
            ->setDeleted(null)
            ->setTaskId($result['taskId'])
            ->setTag($result['tag']);

        return $obj;
    }

    /**
     * @param Task $task
     */
    public static function deleteForTask($task)
    {
        $mysql = MySql::instance();

        $mysql->query('DELETE FROM `TaskTag` WHERE `taskId` = :taskId',
            array(':taskId'=>$task->getId()));
    }

    /**
     * @param Task $task
     * @param string $tag
     * @return TaskTag
     */
    public static function create($task, $tag)
    {
        $obj = new self;

        $obj->setTask($task)
            ->setTag($tag)
            ->insert();

        return $obj;
    }

    protected function insert()
    {
        $mysql = MySql::instance();

        $mysql->query('INSERT INTO `TaskTag` (`taskId`, `tag`) ' .
                      'VALUES (:taskId, :tag)',
                      array(
                          ':taskId'=>$this->taskId,
                          ':tag'=>$this->tag
                      )
        );
    }

    protected function update()
    {
        throw new \Exception('update() not supported on TaskFile');
    }

}