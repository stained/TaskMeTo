<?php

namespace Model;

use Util\MySql;
use Util\Str;

class TaskResponse extends Root
{
    /**
     * @var int
     */
    protected $createdTimestamp;

    /**
     * @var int
     */
    protected $userTaskId;

    /**
     * @var int
     */
    protected $taskId;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $response;

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
     * @return UserTask
     */
    public function getUserTask()
    {
        return UserTask::getById($this->userTaskId);
    }

    /**
     * @param int $userTaskId
     * @return $this
     */
    protected function setUserTaskId($userTaskId)
    {
        $this->userTaskId = $userTaskId;
        return $this;
    }

    /**
     * @param UserTask $userTask
     * @return $this
     */
    public function setUserTask($userTask)
    {
        if ($userTask) {
            $this->userTaskId = $userTask->getId();
        }
        else {
            $this->userTaskId = null;
        }

        return $this;
    }

    /**
     * @return Task
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
     * @return User
     */
    public function getUser()
    {
        return User::getById($this->userId);
    }

    /**
     * @param int $userId
     * @return $this
     */
    protected function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser($user)
    {
        if ($user) {
            $this->userId = $user->getId();
        }
        else {
            $this->userId = null;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param string $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return TaskResponseFile[]
     */
    public function getFiles()
    {
        return TaskResponseFile::getAllForTaskResponse($this);
    }

    /**
     * @string format
     * @return bool|string
     */
    public function getFormattedCreateDate($format = "D, F j Y, H:i:s e")
    {
        return date($format, $this->createdTimestamp);
    }

    /**
     * @return string
     */
    public function getPrettifiedCreateDate()
    {
        return Str::timeToString($this->createdTimestamp);
    }

    /**
     * @param int $id
     * @return TaskResponse|null
     */
    public static function getById($id)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `TaskResponse` WHERE `id` = :id AND `deleted` = 0 LIMIT 1',
            array(':id'=>$id));

        return self::populateOne($result[0]);
    }

    /**
     * @param Task $task
     * @return TaskResponse[]
     */
    public static function getAllForTask($task)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `TaskResponse` WHERE `taskId` = :taskId AND `deleted` = 0 ORDER BY `id`',
            array(':taskId'=>$task->getId()));

        return self::populateMany($result);
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
            ->setDeleted($result['deleted'])
            ->setUserId($result['userId'])
            ->setTaskId($result['taskId'])
            ->setUserTaskId($result['userTaskId'])
            ->setResponse($result['response']);

        return $obj;
    }

    /**
     * @return $this
     */
    public function update()
    {
        $mysql = MySql::instance();

        $result = $mysql->query(
            'UPDATE `TaskResponse` SET `deleted` = :deleted, `createdTimestamp` = :createdTimestamp,  ' .
            '`userId` = :userId, `taskId` = :taskId, `userTaskId` = :userTaskId, `response` = :response ' .
            'WHERE `id` = :id',
            array(
                ':deleted'=>$this->deleted,
                ':createdTimestamp'=>$this->createdTimestamp,
                ':userId'=>$this->userId,
                ':taskId'=>$this->taskId,
                ':userTaskId'=>$this->userTaskId,
                ':response'=>$this->response,
                ':id'=>$this->id
            )
        );

        return $this;
    }

    /**
     * @param User $user
     * @param Task $task
     * @param UserTask $userTask
     * @param string $response
     * @return TaskResponse
     */
    public static function create($user, $task, $userTask, $response)
    {
        $obj = new self;

        $now = time();

        $obj->setCreatedTimestamp($now)
            ->setUser($user)
            ->setTask($task)
            ->setUserTask($userTask)
            ->setResponse($response)
            ->insert();

        return $obj;
    }

    protected function insert()
    {
        $mysql = MySql::instance();

        $result = $mysql->query(array(
            'INSERT INTO `TaskResponse` (`deleted`, `createdTimestamp`, `userId`, `taskId`, `userTaskId`, `response`) ' .
            'VALUES (0, :createdTimestamp, :userId, :taskId, :userTaskId, :response)',
            'SELECT LAST_INSERT_ID() AS id'
        ),
            array(
                array(
                    ':createdTimestamp'=>$this->createdTimestamp,
                    ':userId'=>$this->userId,
                    ':taskId'=>$this->taskId,
                    ':userTaskId'=>$this->userTaskId,
                    ':response'=>$this->response
                ),
                array()
            )
        );

        $this->id = $result[0]['id'];
    }
}