<?php

namespace Model;

use Util\MySql;

class File extends Root
{
    /**
     * @var int
     */
    protected $createdTimestamp;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var bool
     */
    protected $public;

    /**
     * @var string
     */
    protected $originalFilename;

    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * @param string $originalFilename
     * @return $this
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;
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
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     * @return $this
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * @param boolean $public
     * @return $this
     */
    public function setPublic($public)
    {
        $this->public = $public;
        return $this;
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return $this->mimeType == 'image/png' ||
               $this->mimeType == 'image/x-png' ||
               $this->mimeType == 'image/jpeg' ||
               $this->mimeType == 'image/jpg';
    }

    /**
     * @return bool
     */
    public function isDocument()
    {
        return $this->mimeType == 'application/pdf';
    }

    public function update()
    {
        $mysql = MySql::instance();

        $mysql->query(
            'UPDATE `File` SET `deleted` = :deleted, `createdTimestamp` = :createdTimestamp, `path` = :path, ' .
            '`mimeType` = :mimeType, `size` = :fileSize, `public` = :isPublic, `originalFilename` = :originalFilename ' .
            'WHERE `id` = :id',
            array(
                ':deleted'=>$this->deleted,
                ':createdTimestamp'=>$this->createdTimestamp,
                ':path'=>$this->path,
                ':mimeType'=>$this->mimeType,
                ':fileSize'=>$this->size,
                ':isPublic'=>$this->public,
                ':originalFilename'=>$this->originalFilename,
                ':id'=>$this->id
            )
        );

        return $this;
    }

    /**
     * @param int $id
     * @return File|null
     */
    public static function getById($id)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `File` WHERE `id` = :id AND `deleted` = 0 LIMIT 1',
            array(':id'=>$id));

        return self::populateOne($result[0]);
    }

    /**
     * @param array $result
     * @return File
     */
    protected static function populateOne($result)
    {
        $obj = new self;
        $obj->setId($result['id'])
            ->setDeleted($result['deleted'])
            ->setCreatedTimestamp($result['createdTimestamp'])
            ->setPath($result['path'])
            ->setMimeType($result['mimeType'])
            ->setSize($result['size'])
            ->setOriginalFilename($result['originalFilename'])
            ->setPublic($result['public']);

        return $obj;
    }

    /**
     * @param string $path
     * @param string $mimeType
     * @param int $size
     * @param bool $public
     * @param string $originalFilename
     * @return File
     */
    public static function create($path, $mimeType, $size, $public, $originalFilename)
    {
        $obj = new self;
        $obj->setCreatedTimestamp(time())
            ->setPath($path)
            ->setMimeType($mimeType)
            ->setSize($size)
            ->setPublic($public)
            ->setOriginalFilename($originalFilename)
            ->insert();

        return $obj;
    }

    protected function insert()
    {
        $mysql = MySql::instance();

        $result = $mysql->query(array(
            'INSERT INTO `File` (`deleted`, `createdTimestamp`, `path`, `mimeType`, `size`, `public`, `originalFilename`) ' .
            'VALUES (0, :createdTimestamp, :path, :mimeType, :fileSize, :isPublic, :originalFilename)',
            'SELECT LAST_INSERT_ID() AS id'
        ),
            array(
                array(
                    ':createdTimestamp'=>$this->createdTimestamp,
                    ':path'=>$this->path,
                    ':mimeType'=>$this->mimeType,
                    ':fileSize'=>$this->size,
                    ':isPublic'=>$this->public,
                    ':originalFilename'=>$this->originalFilename
                ),
                array()
            )
        );

        $this->id = $result[0]['id'];
    }
}