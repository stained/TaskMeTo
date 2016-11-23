<?php

namespace Controller;

use Model\User;

class Root
{
    /**
     * @var User
     */
    protected static $user;

    /**
     * @var array
     */
    protected static $protectedRoutes = array();

    /**
     * @param \Base $f3
     */
    public static function beforeRoute($f3)
    {
        $pieces = explode('?', $f3->get('URI'));
        $actualRoute = $pieces[0];
        $f3->set('route', $actualRoute);

        $loginToken = $f3->get('COOKIE.user_token');

        if ($loginToken) {
            static::$user = User::getByLoginToken($loginToken);
            $f3->set('user', static::$user);
        }

        if (in_array($actualRoute, static::$protectedRoutes)) {
            if (!static::$user) {
                $f3->set('error', 'Please login to view this page');
                $f3->set('REQUEST.returnpath', $actualRoute);
                \Controller\User::signIn($f3);
            }
        }
    }

    /**
     * @param \Base $f3
     */
    public static function index($f3)
    {
        // reroute to task list
        $f3->reroute('/tasks');
    }

    /**
     * @param \Base $f3
     */
    public static function error($f3)
    {
        static::render($f3, 'root/error', array('nav'=>array('Home' => '/', 'Oh noes!' => '')));
    }

    /**
     * @param \Base $f3
     */
    public static function fourOhFour($f3)
    {
        $f3->set('ERROR.text', 'Sorry, that page could not be found.');
        static::error($f3);
    }

    /**
     * @param \Base $f3
     */
    public static function fourOhThree($f3)
    {
        $f3->set('ERROR.text', "Sorry Dave I can't let you do that.");
        static::error($f3);
    }

    /**
     * @param \Base $f3
     * @param string $template
     * @param array $variables
     */
    protected static function render($f3, $template, $variables = array())
    {
        foreach ($variables as $key=>$value) {
            $f3->set($key, $value);
        }

        $f3->set('content', "{$template}.htm");

        echo \Template::instance()->render('default.htm');
        die();
    }
}