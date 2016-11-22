<?php

namespace Model;


class UserTask extends Root
{
    /**
     * @param User $user
     * @return Task[]|null
     */
    public static function getCurrentForUser($user)
    {
        return null;
    }

    /**
     * @param User $user
     * @return Task[]|null
     */
    public static function getCompletedForUser($user)
    {
        return null;
    }
}