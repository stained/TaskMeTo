<?php

namespace Controller;

use Model\Task;
use Model\UserTask;
use Util\Arr;

class User extends Root
{
    protected static $protectedRoutes = array(
        '/user/profile/edit'
    );

    /**
     * @param \Base $f3
     */
    public static function viewProfile($f3)
    {
        $username = $f3->get('PARAMS.username');

        if (!$username) {
            if (!static::$user) {
                parent::fourOhFour($f3);
            }
            else {
                $username = static::$user->getUsername();
            }
        }

        $displayName = $username;

        if (static::$user && $username == static::$user->getUsername()) {
            $f3->set('message', 'Welcome back ' . static::$user->getUsername() . '!');
            $displayName = 'You';
            $user = static::$user;
            $f3->set('isOwner', true);
            $createdTasks = Task::getCreatedForUser($user);
        }
        else {
            $user = \Model\User::getByUsername($username);

            if (!$user) {
                parent::fourOhFour($f3);
            }

            $f3->set('isOwner', false);
            $createdTasks = Task::getCreatedAndPublishedForUser($user);
        }

        $currentTasks = Task::getCurrentForUser($user);
        $completedTasks = Task::getCompletedForUser($user);

        $f3->set('currentTasks', $currentTasks);
        $f3->set('completedTasks', $completedTasks);
        $f3->set('createdTasks', $createdTasks);
        $f3->set('username', $username);
        $f3->set('points', UserTask::getCompletedCountForUser($user));

        static::render($f3, 'user/profile', array('nav'=>array('Home' => '/', $displayName=>'')));
    }

    public static function editProfile($f3)
    {
        static::render($f3, 'user/edit', array('nav'=>array('Home' => '/',
                                                            static::$user->getUsername()=>'/user/profile/' . static::$user->getUsername(),
                                                            'Edit' => ''
        )));
    }

    /**
     * @param \Base $f3
     */
    public static function signIn($f3)
    {
        $nav = array('Home' => '/', 'Sign In'=>'');

        $post = $f3->get('POST');

        if (!$post) {
            static::render($f3, 'user/signin', array('nav'=>$nav));
        }

        $username = $post['username'];
        $password = $post['password'];

        if (!$username || !$password) {
            static::render($f3, 'user/signin', array('nav'=>$nav,
                                                     'error' => 'Invalid username or password'
            ));
        }

        $user = \Model\User::getByUsername($username);

        if (!$user) {
            static::render($f3, 'user/signin', array('nav'=>$nav,
                'error' => 'Invalid username or password'
            ));
        }

        if (!$user->validatePassword($password)) {
            static::render($f3, 'user/signin', array('nav'=>$nav,
                'error' => 'Invalid username or password'
            ));
        }

        self::login($f3, $user);
    }

    /**
     * @param \Base $f3
     */
    public static function register($f3)
    {
        $post = $f3->get('POST');

        $nav = array('Home' => '/', 'Register'=>'');
        $template = 'user/register';

        if (!$post) {
            static::render($f3, $template, array('nav'=>$nav));
        }

        $username = strip_tags(trim(Arr::get($post, 'username', '')));
        $password = $post['password'];
        $email = $post['email'];

        // validate username
        if (!$username) {
            static::render($f3, $template, array('nav'=>$nav,
                'error' => 'Invalid username'
            ));
        }

        // validate password
        if (!$password) {
            static::render($f3, $template, array('nav'=>$nav,
                'error' => 'Invalid password'
            ));
        }

        // validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            static::render($f3, $template, array('nav'=>$nav,
                'error' => 'Invalid email address'
            ));
        }

        $user = \Model\User::getByUsername($username);

        if ($user) {
            static::render($f3, $template, array('nav'=>$nav,
                'error' => 'A user account already exists with that username'
            ));
        }

        $user = \Model\User::getByEmail($email);

        if ($user) {
            static::render($f3, $template, array('nav'=>$nav,
                'error' => 'A user account already exists with that email'
            ));
        }

        // create account
        $user = \Model\User::create($username, $password, $email);

        if (!$user) {
            static::render($f3, $template, array('nav'=>$nav,
                'error' => 'Something went wrong, please try again later'
            ));
        }

        // login and go to profile
        self::login($f3, $user);
    }

    /**
     * @param \Base $f3
     */
    public static function signOut($f3)
    {
        $f3->clear('COOKIE.user_token');
        $f3->reroute("/");
    }

    /**
     * @param \Base $f3
     * @param \Model\User $user
     */
    private static function login($f3, $user)
    {
        static::$user = $user;

        // generate login token
        $loginToken = sha1(uniqid('u', true));
        $user->setLoginToken($loginToken)->update();

        $f3->set('COOKIE.user_token', $loginToken, 60 * 60 * 24 * 30);

        $returnPath = $f3->get('REQUEST.returnpath');

        if ($returnPath) {
            $f3->reroute($returnPath);
        }

        $f3->reroute("/user/profile/{$user->getUsername()}");
    }
}