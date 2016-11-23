<?php

namespace Model;


use Util\MySql;

class TaskFile extends Root
{
    /**
     * @var int
     */
    protected $fileId;

    /**
     * @var int
     */
    protected $taskId;

    /**
     * @throws \Exception
     */
    public function isDeleted()
    {
        throw new \Exception('isDeleted() not supported on TaskFile');
    }

    /**
     * @throws \Exception
     */
    public function getId()
    {
        throw new \Exception('getId() not supported on TaskFile');
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return File::getById($this->fileId);
    }

    /**
     * @param int $fileId
     * @return $this
     */
    public function setFileId($fileId)
    {
        $this->fileId = $fileId;
        return $this;
    }

    /**
     * @param File $file
     * @return $this
     */
    public function setFile($file)
    {
        if ($file) {
            $this->fileId = $file->getId();
        }
        else {
            $this->fileId = null;
        }

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
     * @param Task $task
     * @return TaskFile[]
     */
    public static function getAllForTask($task)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `TaskFile` WHERE `taskId` = :taskId',
            array(':taskId'=>$task->getId()));

        return self::populateMany($result);
    }

    /**
     * @param array $result
     * @return TaskFile|null
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
            ->setFileId($result['fileId']);

        return $obj;
    }

    /**
     * @param Task $task
     */
    public static function deleteForTask($task)
    {
        $mysql = MySql::instance();

        $mysql->query('DELETE FROM `TaskFile` WHERE `taskId` = :taskId',
            array(':taskId'=>$task->getId()));
    }

    /**
     * @param Task $task
     * @param File $file
     * @return TaskFile
     */
    public static function create($task, $file)
    {
        $obj = new self;

        $obj->setTask($task)
            ->setFile($file)
            ->insert();

        return $obj;
    }

    protected function insert()
    {
        $mysql = MySql::instance();

        $mysql->query('INSERT INTO `TaskFile` (`taskId`, `fileId`) ' .
                      'VALUES (:taskId, :fileId)',
                      array(
                          ':taskId'=>$this->taskId,
                          ':fileId'=>$this->fileId
                      )
        );
    }


}