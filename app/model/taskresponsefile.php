<?php

namespace Model;


use Util\MySql;

class TaskResponseFile extends Root
{
    /**
     * @var int
     */
    protected $fileId;

    /**
     * @var int
     */
    protected $taskResponseId;

    /**
     * @throws \Exception
     */
    public function isDeleted()
    {
        throw new \Exception('isDeleted() not supported on TaskResponseFile');
    }

    /**
     * @throws \Exception
     */
    public function getId()
    {
        throw new \Exception('getId() not supported on TaskResponseFile');
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
     * @return TaskResponse|null
     */
    public function getTaskResponse()
    {
        return TaskResponse::getById($this->taskResponseId);
    }

    /**
     * @param int $taskResponseId
     * @return $this
     */
    protected function setTaskResponseId($taskResponseId)
    {
        $this->taskResponseId = $taskResponseId;
        return $this;
    }

    /**
     * @param TaskResponse $taskResponse
     * @return $this
     */
    public function setTaskResponse($taskResponse)
    {
        if ($taskResponse) {
            $this->taskResponseId = $taskResponse->getId();
        }
        else {
            $this->taskResponseId = null;
        }

        return $this;
    }

    /**
     * @param TaskResponse $taskResponse
     * @param File $file
     * @return TaskResponseFile
     */
    public static function getForTaskAndFile($taskResponse, $file)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `TaskResponseFile` WHERE `taskResponseId` = :taskResponseId AND `fileId` = :fileId',
            array(':taskResponseId'=>$taskResponse->getId(), ':fileId'=>$file->getId()));

        return self::populateOne($result[0]);
    }

    /**
     * @param TaskResponse $taskResponse
     * @return TaskResponseFile[]
     */
    public static function getAllForTaskResponse($taskResponse)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `TaskResponseFile` WHERE `taskResponseId` = :taskResponseId',
            array(':taskResponseId'=>$taskResponse->getId()));

        return self::populateMany($result);
    }

    /**
     * @param array $result
     * @return TaskResponseFile|null
     */
    protected static function populateOne($result)
    {
        if (!$result) {
            return null;
        }

        $obj = new self;

        $obj->setId(null)
            ->setDeleted(null)
            ->setTaskResponseId($result['taskResponseId'])
            ->setFileId($result['fileId']);

        return $obj;
    }

    /**
     * @param TaskResponse $taskResponse
     */
    public static function deleteForTaskResponse($taskResponse)
    {
        $mysql = MySql::instance();

        $mysql->query('DELETE FROM `TaskResponseFile` WHERE `taskResponseId` = :taskResponseId',
            array(':taskResponseId'=>$taskResponse->getId()));
    }

    /**
     * @param TaskResponse $taskResponse
     * @param File $file
     * @return TaskResponseFile
     */
    public static function create($taskResponse, $file)
    {
        $obj = new self;

        $obj->setTaskResponse($taskResponse)
            ->setFile($file)
            ->insert();

        return $obj;
    }

    protected function insert()
    {
        $mysql = MySql::instance();

        $mysql->query('INSERT INTO `TaskResponseFile` (`taskResponseId`, `fileId`) ' .
                      'VALUES (:taskResponseId, :fileId)',
                      array(
                          ':taskResponseId'=>$this->taskResponseId,
                          ':fileId'=>$this->fileId
                      )
        );
    }

    public function delete()
    {
        $mysql = MySql::instance();

        $mysql->query('DELETE FROM `TaskResponseFile` WHERE `taskResponseId` = :taskResponseId AND `fileId` = :fileId',
            array(
                ':taskResponseId'=>$this->taskResponseId,
                ':fileId'=>$this->fileId
            )
        );
    }

    protected function update()
    {
        throw new \Exception('update() not supported on TaskResponseFile');
    }
}