<?php

namespace Controller;

use Model\File;
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
     * @param bool $public
     * @return File[]|null
     */
    protected static function handleFileUpload($f3, $public = false)
    {
        $f3->set('UPLOADS', $f3->get('paths.file_upload_path'));

        $web = \Web::instance();

        $files = $web->receive(function($file, $formFieldName){
            // TODO move out into more generic location
            $allowedTypes = array(
                // images
                'image/png',
                'image/jpeg',
                'image/x-png',
                'image/jpg',
                // documents
                'application/pdf',
                // archives
                'application/x-7z-compressed',
                'application/x-rar-compressed',
                'application/zip',
                'application/x-gtar',
                'application/x-compressed',
                'application/x-gzip',
                'application/x-bzip2'
            );

            if (!in_array($file['type'], $allowedTypes)) {
                // type not allowed
                return false;
            }

            /* looks like:
              array(5) {
                  ["name"] =>     string(19) "csshat_quittung.png"
                  ["type"] =>     string(9) "image/png"
                  ["tmp_name"] => string(14) "/tmp/php2YS85Q"
                  ["error"] =>    int(0)
                  ["size"] =>     int(172245)
                }
            */
            // $file['name'] already contains the slugged name now

            // maybe you want to check the file size
            if ($file['size'] == 0 || $file['size'] > (50 * 1024 * 1024)) {
                // if bigger than 50 MB
                return false; // this file is not valid, return false will skip moving it
            }

            // everything went fine, hurray!
            return true; // allows the file to be moved from php tmp dir to your defined upload dir
        }, false, true);

        $fileObjects = array();

        if ($files) {
            foreach ($files as $filePath=>$success) {
                if ($success) {

                    $pathInfo = pathInfo($filePath);
                    $extension = $pathInfo['extension'];
                    $originalFilename = $pathInfo['filename'] . '.' . $extension;

                    // create file
                    $file = File::create(
                        $filePath,
                        mime_content_type($filePath),
                        filesize($filePath),
                        $public,
                        $originalFilename
                    );

                    // rename
                    $newFilePath = $f3->get('paths.file_upload_path') . uniqid('f' . $file->getId() . '_', true)  . '.' . $extension;

                    if(rename($filePath, $newFilePath)) {
                        // if move succeeded update file
                        $file->setPath($newFilePath)->update();
                    }

                    $fileObjects[] = $file;
                }
            }
        }

        return $fileObjects;
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