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

    public function delete()
    {
        $this->setDeleted(true)->update();
    }

    /**
     * @param array $result
     * @return static
     */
    protected static function populateMany($result)
    {
        if (!$result) {
            return array();
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

    protected static function populateOne($result)
    {

    }

    protected abstract function update();
}