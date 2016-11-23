<?php

namespace Model;

use Util\MySql;

class User extends Root
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
    protected $username;

    /**
     * @var string
     */
    protected $passwordHash;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $loginToken;

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
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @param string $passwordHash
     * @return $this
     */
    protected function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $this->setPasswordHash($passwordHash);
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getLoginToken()
    {
        return $this->loginToken;
    }

    /**
     * @param string $loginToken
     * @return $this
     */
    public function setLoginToken($loginToken)
    {
        $this->loginToken = $loginToken;
        return $this;
    }

    /**
     * @param string $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return password_verify($password, $this->passwordHash);
    }

    /**
     * @param string $username
     * @return User|null
     */
    public static function getByUsername($username)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `User` WHERE `username` = :username AND `deleted` = 0 LIMIT 1',
                                array(':username'=>$username));

        return self::populateOne($result[0]);
    }

    /**
     * @param string $email
     * @return User|null
     */
    public static function getByEmail($email)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `User` WHERE `email` = :email AND `deleted` = 0 LIMIT 1',
            array(':email'=>$email));

        return self::populateOne($result[0]);
    }

    /**
     * @param int $id
     * @return User|null
     */
    public static function getById($id)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `User` WHERE `id` = :id AND `deleted` = 0 LIMIT 1',
            array(':id'=>$id));

        return self::populateOne($result[0]);
    }

    /**
     * @param string $loginToken
     * @return User|null
     */
    public static function getByLoginToken($loginToken)
    {
        $mysql = MySql::instance();

        $result = $mysql->query('SELECT * FROM `User` WHERE `loginToken` = :loginToken AND `deleted` = 0 LIMIT 1',
            array(':loginToken'=>$loginToken));

        return self::populateOne($result[0]);
    }

    /**
     * @param array $result
     * @return User
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
            ->setUsername($result['username'])
            ->setPasswordHash($result['passwordHash'])
            ->setLoginToken($result['loginToken'])
            ->setEmail($result['email']);

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
            'UPDATE `User` SET `deleted` = :deleted, `createdTimestamp` = :createdTimestamp, ' .
            '`updatedTimestamp` = :updatedTimestamp, `loginToken` = :loginToken, `username` = :username, ' .
            '`passwordHash` = :passwordHash, `email` = :email WHERE `id` = :id',
            array(
                ':deleted'=>$this->deleted,
                ':createdTimestamp'=>$this->createdTimestamp,
                ':updatedTimestamp'=>$this->updatedTimestamp,
                ':username'=>$this->username,
                ':loginToken'=>$this->loginToken,
                ':passwordHash'=>$this->passwordHash,
                ':email'=>$this->email,
                ':id'=>$this->id
            )
        );

        return $this;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @return User
     */
    public static function create($username, $password, $email)
    {
        $obj = new self;

        $now = time();

        $obj->setUsername($username)
            ->setPassword($password)
            ->setEmail($email)
            ->setCreatedTimestamp($now)
            ->setUpdatedTimestamp($now)
            ->setLoginToken(null)
            ->insert();

        return $obj;
    }

    protected function insert()
    {
        $mysql = MySql::instance();

        $result = $mysql->query(array(
                                    'INSERT INTO `User` (`deleted`, `createdTimestamp`, `updatedTimestamp`, `loginToken`, `username`, `passwordHash`, `email`) ' .
                                    'VALUES (0, :createdTimestamp, :updatedTimestamp, :loginToken, :username, :passwordHash, :email)',
                                    'SELECT LAST_INSERT_ID() AS id'
                                ),
                                array(
                                    array(
                                        ':createdTimestamp'=>$this->createdTimestamp,
                                        ':updatedTimestamp'=>$this->updatedTimestamp,
                                        ':username'=>$this->username,
                                        ':loginToken'=>$this->loginToken,
                                        ':passwordHash'=>$this->passwordHash,
                                        ':email'=>$this->email
                                    ),
                                    array()
                                )
        );

        $this->id = $result[0]['id'];
    }

}