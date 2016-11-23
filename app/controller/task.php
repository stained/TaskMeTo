<?php

namespace Controller;

use Model\TaskFile;
use Model\TaskTag;
use Model\UserTask;

class Task extends Root
{
    protected static $protectedRoutes = array(
        '/task/create',
        '/tasks/view',
        '/task/delete',
        '/task/edit',
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

        static::render($f3, 'task/list', array('tasks'=>$openTasks, 'nav'=>array('Home'=>'/', 'Tasks'=>'')));
    }

    /**
     * @param \Base $f3
     */
    public static function create($f3)
    {
        $nav = array('Home' => '/', 'Create Task'=>'');

        $post = $f3->get('POST');

        if (!$post) {
            static::render($f3, 'task/create', array('nav'=>$nav));
        }

        $files = static::handleFileUpload($f3, true);

        $title = strip_tags($post['title'] ? $post['title'] : '');
        $instructions = strip_tags($post['instructions'] ? $post['instructions'] : '', '<p><br><b><i><u>');
        $deadline = $post['deadline'] ? $post['deadline'] : '';

        if (!$title || !$instructions || !$deadline) {
            static::render($f3, 'task/create', array('nav'=>$nav,
                'error' => 'Please enter a title, basic instructions, and a deadline date at minimum.'
            ));
        }

        $requirements = strip_tags($post['requirements'] ? $post['requirements'] : '', '<p><br><b><i><u><li><ul>');

        $format = "Y/m/d H:i:s";
        $deadlineDate = \DateTime::createFromFormat($format, $deadline);

        if (!$deadlineDate || time() >= $deadlineDate->getTimestamp()) {
            static::render($f3, 'task/create', array('nav'=>$nav,
                'error' => 'Please enter a valid deadline date.'
            ));
        }

        $task = \Model\Task::create($title, $instructions, static::$user);
        $task->setRequirements($requirements)
             ->setDeadlineTimestamp($deadlineDate->getTimestamp())->update();

        // check for tags
        $tags = explode(',', strip_tags($post['tags'] ? $post['tags'] : ''));

        if ($tags) {
            foreach ($tags as $tag) {
                $tag = trim($tag);

                if ($tag) {
                    TaskTag::create($task, $tag);
                }
            }
        }

        if ($files) {
            foreach ($files as $file) {
                // create task file
                TaskFile::create($task, $file);
            }
        }

        $f3->reroute('/task/view/' . $task->getId());
    }


    /**
     * @param \Base $f3
     */
    public static function viewYours($f3)
    {
        $currentTasks = UserTask::getCurrentForUser(static::$user);
        $completedTasks = UserTask::getCompletedForUser(static::$user);
        $createdTasks = \Model\Task::getCreatedForUser(static::$user);

        $f3->set('currentTasks', $currentTasks);
        $f3->set('completedTasks', $completedTasks);
        $f3->set('createdTasks', $createdTasks);
        $f3->set('isOwner', true);
        $f3->set('username', static::$user->getUsername());

        static::render($f3, 'task/yours', array('nav'=>array('Home'=>'/', 'Your Tasks'=>'')));
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

        $f3->set('task', $task);

        if (static::$user && $task->getCreatedByUser()->getId() == static::$user->getId()) {
            $f3->set('isOwner', true);
            $nav = array('Home'=>'/', 'Your Tasks'=>'/tasks/view', $task->getTitle()=>'');
        }
        else {
            $f3->set('isOwner', false);
            $nav = array('Home'=>'/', 'Viewing ' . $task->getTitle()=>'');
        }

        $f3->set('tags', $task->getTags());
        $f3->set('files', $task->getFiles());

        static::render($f3, 'task/view', array('nav'=>$nav));
    }

    /**
     * @param \Base $f3
     */
    public static function editTask($f3)
    {
        $taskId = $f3->get('PARAMS.id');

        $task = \Model\Task::getById($taskId);
        self::validateOwner($f3, $task);

        static::render($f3, 'task/edit', array('nav'=>array('Home'=>'/', 'Your Tasks'=>'/tasks/view',
                                                            $task->getTitle()=>'/task/view/' . $task->getId(),
                                                            'Edit'=>'')));
    }

    /**
     * @param \Base $f3
     */
    public static function deleteTask($f3)
    {
        $taskId = $f3->get('PARAMS.id');

        $task = \Model\Task::getById($taskId);
        self::validateOwner($f3, $task);

        // get files
        $taskFiles = TaskFile::getAllForTask($task);

        if (!empty($taskFiles)) {
            foreach ($taskFiles as $taskFile) {
                $file = $taskFile->getFile();

                if ($file) {
                    $file->setDeleted(true)->update();
                }
            }
        }

        TaskTag::deleteForTask($task);
        TaskFile::deleteForTask($task);

        $task->setDeleted(true)->update();

        $f3->set('message', "Task \"{$task->getTitle()}\" successfully deleted");

        static::viewYours($f3);
    }

    /**
     * @param \Base $f3
     */
    public static function publishTask($f3)
    {
        $taskId = $f3->get('PARAMS.id');

        $task = \Model\Task::getById($taskId);
        self::validateOwner($f3, $task);

        $task->setPublished(true)->update();

        $f3->set('message', "Task \"{$task->getTitle()}\" successfully published");

        static::viewYours($f3);
    }

    /**
     * @param \Base $f3
     */
    public static function unpublishTask($f3)
    {
        $taskId = $f3->get('PARAMS.id');

        $task = \Model\Task::getById($taskId);
        self::validateOwner($f3, $task);

        $task->setPublished(false)->update();

        $f3->set('message', "Task \"{$task->getTitle()}\" successfully unpublished");

        static::viewYours($f3);
    }

    /**
     * @param \Model\Task $task
     */
    private static function validateOwner($f3, $task)
    {
        if (!$task) {
            parent::fourOhFour($f3);
        }

        if ($task->getCreatedByUserId() != static::$user->getId()) {
            parent::fourOhThree($f3);
        }
    }
}