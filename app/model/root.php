<?php

namespace Model;

abstract class Root
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var bool
     */
    protected $deleted = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param boolean $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @param array $result
     * @return static
     */
    protected static function populateMany($result)
    {
        if (!$result) {
            return null;
        }

        $many = [];

        foreach ($result as $item) {
            $obj = static::populateOne($item);

            if ($obj) {
                $many[] = $obj;
            }
        }

        return $many;
    }

    protected abstract static function populateOne($result);
}