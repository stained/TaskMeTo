<?php

namespace Controller;

use Model\File;
use Model\TaskFile;
use Model\TaskTag;
use Model\UserTask;

class Task extends Root
{
    protected static $protectedRoutes = array(
        '/task/create',
        '/tasks/view',
        '/task/delete/@id',
        '/task/edit/@id',
        '/task/subscribe/@id',
        '/task/unsubscribe/@id',
        '/task/edit/@id/remove/@fileId'
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
        $currentTasks = \Model\Task::getCurrentForUser(static::$user);
        $completedTasks = \Model\Task::getCompletedForUser(static::$user);
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

        if (static::$user && $task->getCreatedByUserId() == static::$user->getId()) {
            $f3->set('isOwner', true);
            $nav = array('Home'=>'/', 'Your Tasks'=>'/tasks/view', $task->getTitle()=>'');
        }
        else {
            $f3->set('isOwner', false);
            $nav = array('Home'=>'/', 'Viewing ' . $task->getTitle()=>'');
        }

        if (static::$user) {
            $userTask = UserTask::getForUserAndTask(static::$user, $task);
            $f3->set('subscribedToTask', !empty($userTask));
        }

        $f3->set('subscribers', UserTask::getAllForTask($task));

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

        $f3->set('task', $task);

        $taskTags = TaskTag::getAllForTask($task);

        $tags = array();

        foreach ($taskTags as $taskTag) {
            $tags[] = $taskTag->getTag();
        }

        $f3->set('tags', $tags);

        $f3->set('files', TaskFile::getAllForTask($task));

        static::render($f3, 'task/edit', array('nav'=>array('Home'=>'/', 'Your Tasks'=>'/tasks/view',
                                                            $task->getTitle()=>'/task/view/' . $task->getId(),
                                                            'Edit'=>'')));
    }

    /**
     * @param \Base $f3
     */
    public static function updateTask($f3)
    {
        $taskId = $f3->get('PARAMS.id');

        $task = \Model\Task::getById($taskId);
        self::validateOwner($f3, $task);

        $post = $f3->get('POST');

        if (!$post) {
            self::editTask($f3);
        }

        $nav = array('Home'=>'/', 'Your Tasks'=>'/tasks/view',
                    $task->getTitle()=>'/task/view/' . $task->getId(),
                    'Edit'=>'');

        $files = static::handleFileUpload($f3, true);

        $title = strip_tags($post['title'] ? $post['title'] : '');
        $instructions = strip_tags($post['instructions'] ? $post['instructions'] : '', '<p><br><b><i><u>');
        $deadline = $post['deadline'] ? $post['deadline'] : '';

        if (!$title || !$instructions || !$deadline) {
            static::render($f3, 'task/edit', array('nav'=>$nav,
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

        $task->setTitle($title)
             ->setInstructions($instructions)
             ->setRequirements($requirements)
             ->setDeadlineTimestamp($deadlineDate->getTimestamp())
             ->update();

        TaskTag::deleteForTask($task);

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

        $f3->set('message', "Task \"{$task->getTitle()}\" successfully updated");

        self::editTask($f3);
    }

    /**
     * @param \Base $f3
     */
    public static function editTaskRemoveFile($f3)
    {
        $taskId = $f3->get('PARAMS.id');

        $task = \Model\Task::getById($taskId);
        self::validateOwner($f3, $task);

        $fileId = $f3->get('PARAMS.fileId');

        $file = File::getById($fileId);

        if (!$file) {
            parent::fourOhFour($f3);
        }

        $taskFile = TaskFile::getForTaskAndFile($task, $file);

        if (!$taskFile) {
            parent::fourOhThree($f3);
        }

        $file->delete();
        $taskFile->delete();

        $f3->set('message', "File \"{$file->getOriginalFilename()}\" successfully removed");

        self::editTask($f3);
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
                    $file->delete();
                }
            }
        }

        TaskTag::deleteForTask($task);
        TaskFile::deleteForTask($task);

        $task->delete();

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
     * @param \Base $f3
     */
    public static function subscribeToTask($f3)
    {
        $taskId = $f3->get('PARAMS.id');

        $task = \Model\Task::getById($taskId);

        if (!$task) {
            parent::fourOhFour($f3);
        }

        // check check if user has already subscribed
        $userTask = UserTask::getForUserAndTask(static::$user, $task);

        if ($userTask) {
            if ($userTask->isComplete()) {
                $f3->set('message', "You have already completed \"{$task->getTitle()}\"");
            }
            else {
                $f3->set('message', "You are already subscribed to \"{$task->getTitle()}\"");
            }
        }
        else {
            if ($task->hasDeadlinePassed()) {
                $f3->set('error', "Deadline has passed, so you can no longer subscribe to this task");
            }
            else {
                // subscribe!
                $userTask = UserTask::create(static::$user, $task, uniqid('ut', true));
                $userTask->setAcceptedTimestamp(time())->update();
                $f3->set('message', "You have successfully subscribed to \"{$task->getTitle()}\"");
            }
        }

        static::viewTask($f3);
    }

    /**
     * @param \Base $f3
     */
    public static function unsubscribeFromTask($f3)
    {
        $taskId = $f3->get('PARAMS.id');

        $task = \Model\Task::getById($taskId);

        if (!$task) {
            parent::fourOhFour($f3);
        }

        // check check if user has subscribed
        $userTask = UserTask::getForUserAndTask(static::$user, $task);

        if (!$userTask) {
            $f3->set('error', "You are not currently subscribed to \"{$task->getTitle()}\"");
        }
        else {
            if ($userTask->isComplete()) {
                $f3->set('message', "You have already completed \"{$task->getTitle()}\"");
            }
            else {
                // un subscribe!
                $userTask->delete();
                $f3->set('message', "You have successfully unsubscribed from \"{$task->getTitle()}\"");
            }
        }

        static::viewTask($f3);
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