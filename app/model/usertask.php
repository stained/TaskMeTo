<?php

namespace Model;


use Util\MySql;

class UserTask extends Root
{
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
    protected $acceptanceToken;

    /**
     * @var int
     */
    protected $completedTimestamp = 0;

    /**
     * @var int
     */
    protected $acceptedTimestamp = 0;

    /**
     * @return int
     */
    public function getCompletedTimestamp()
    {
        return $this->completedTimestamp;
    }

    /**
     * @param int $completedTimestamp
     * @return $this
     */
    public function setCompletedTimestamp($completedTimestamp)
    {
        $this->completedTimestamp = $completedTimestamp;
        return $this;
    }

    /**
     * @return int
     */
    public function getAcceptedTimestamp()
    {
        return $this->acceptedTimestamp;
    }

    /**
     * @param int $acceptedTimestamp
     * @return $this
     */
    public function setAcceptedTimestamp($acceptedTimestamp)
    {
        $this->acceptedTimestamp = $acceptedTimestamp;
        return $this;
    }

    public function isComplete()
    {
        return $this->completedTimestamp > 0;
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
    public function getAcceptanceToken()
    {
        return $this->acceptanceToken;
    }

    /**
     * @param string $acceptanceToken
     * @return $this
     */
    public function setAcceptanceToken($acceptanceToken)
    {
        $this->acceptanceToken = $acceptanceToken;
        return $this;
    }

    /**
     * @param User $user
     * @param Task $task
     * @return UserTask|null
     */
    public static function getForUserAndTask($user, $task)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `UserTask` WHERE `userId` = :userId AND `taskId` = :taskId ' .
                                'AND `deleted` = 0 ORDER BY `id` DESC LIMIT 1',
            array(
                ':userId'=>$user->getId(),
                ':taskId'=>$task->getId()
            ));

        return self::populateOne($result[0]);
    }

    /**
     * @param Task $task
     * @return UserTask[]
     */
    public static function getAllForTask($task)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `UserTask` WHERE `taskId` = :taskId ' .
            'AND `deleted` = 0 ORDER BY `acceptedTimestamp` DESC',
            array(
                ':taskId'=>$task->getId()
            ));

        return self::populateMany($result);
    }

    /**
     * @param Task $task
     * @return int
     */
    public static function getCountForTask($task)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT COUNT(*) AS `subscribers` FROM `UserTask` WHERE `taskId` = :taskId ' .
            'AND `deleted` = 0',
            array(
                ':taskId'=>$task->getId()
            ));

        return $result[0]['subscribers'];
    }

    /**
     * @param User $user
     * @return UserTask[]|null
     */
    public static function getCurrentForUser($user)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `UserTask` WHERE `userId` = :userId AND `completedTimestamp` = 0 AND `deleted` = 0',
            array(':userId'=>$user->getId()));

        return self::populateMany($result);
    }

    /**
     * @param User $user
     * @return UserTask[]|null
     */
    public static function getCompletedForUser($user)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `UserTask` WHERE `userId` = :userId AND `completedTimestamp` > 0 AND `deleted` = 0',
            array(':userId'=>$user->getId()));

        return self::populateMany($result);
    }

    /**
     * @param array $result
     * @return UserTask|null
     */
    protected static function populateOne($result)
    {
        if (!$result) {
            return null;
        }

        $obj = new self;

        $obj->setId($result['id'])
            ->setDeleted($result['deleted'])
            ->setUserId($result['userId'])
            ->setTaskId($result['taskId'])
            ->setAcceptanceToken($result['acceptanceToken'])
            ->setAcceptedTimestamp($result['acceptedTimestamp'])
            ->setCompletedTimestamp($result['completedTimestamp']);

        return $obj;
    }

    public function update()
    {
        $mysql = MySql::instance();

        $mysql->query(
            'UPDATE `UserTask` SET `deleted` = :deleted, `completedTimestamp` = :completedTimestamp, `acceptedTimestamp` = :acceptedTimestamp, ' .
            '`userId` = :userId, `taskId` = :taskId, `acceptanceToken` = :acceptanceToken ' .
            'WHERE `id` = :id',
            array(
                ':deleted'=>$this->deleted,
                ':completedTimestamp'=>$this->completedTimestamp,
                ':acceptedTimestamp'=>$this->acceptedTimestamp,
                ':userId'=>$this->userId,
                ':taskId'=>$this->taskId,
                ':acceptanceToken'=>$this->acceptanceToken,
                ':id'=>$this->id
            )
        );

        return $this;
    }

    /**
     * @param $user
     * @param $task
     * @param $acceptanceToken
     * @return UserTask
     */
    public static function create($user, $task, $acceptanceToken)
    {
        $obj = new self;

        $obj->setUser($user)
            ->setTask($task)
            ->setAcceptanceToken($acceptanceToken)
            ->insert();

        return $obj;
    }

    protected function insert()
    {
        $mysql = MySql::instance();

        $result = $mysql->query(array(
            'INSERT INTO `UserTask` (`deleted`, `userId`, `taskId`, `acceptedTimestamp`, `completedTimestamp`, `acceptanceToken`) ' .
            'VALUES (0, :userId, :taskId, 0, 0, :acceptanceToken)',
            'SELECT LAST_INSERT_ID() AS id'
        ),
            array(
                array(
                    ':userId'=>$this->userId,
                    ':taskId'=>$this->taskId,
                    ':acceptanceToken'=>$this->acceptanceToken
                ),
                array()
            )
        );

        $this->id = $result[0]['id'];
    }
}