<?php

namespace Controller;

class Task extends Root
{
    protected static $protectedRoutes = array(
        '/task/create',
        '/task/view'
    );

    /**
     * @param \Base $f3
     */
    public static function taskList($f3)
    {
        $tag = $f3->get('REQUEST.tag');

        if ($tag) {
            $openTasks = \Model\Task::getOpenForTag($tag);
        }
        else {
            $openTasks = \Model\Task::getOpen();
        }

        static::render($f3, 'task/list', array('openTasks'=>$openTasks, 'nav'=>array('Home'=>'/', 'Tasks'=>'')));
    }

    /**
     * @param \Base $f3
     */
    public static function create($f3)
    {
        static::render($f3, 'task/create', array('nav'=>array('Home'=>'/', 'Create Task'=>'')));
    }

    /**
     * @param \Base $f3
     */
    public static function viewCreated($f3)
    {
        $f3->set('tasks', \Model\Task::getForUser(static::$user));

        static::render($f3, 'task/created', array('nav'=>array('Home'=>'/', 'View Tasks'=>'')));
    }

    /**
     * @param \Base $f3
     */
    public static function viewTask($f3)
    {
        $taskId = $f3->get('PARAMS.id');

        $task = \Model\Task::getById($taskId);

        if (!$task) {
            parent::fourOhFour($f3);
        }

        static::render($f3, 'task/view', array('nav'=>array('Home'=>'/', 'View Tasks'=>'')));
    }
}