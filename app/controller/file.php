<?php

namespace Controller;

class File extends Root
{
    /**
     * @param \Base $f3
     */
    public static function get($f3)
    {
        $fileId = $f3->get('PARAMS.id');
        $file = \Model\File::getById($fileId);

        if (!$file) {
            parent::fourOhFour($f3);
        }

        if (!$file->isPublic()) {
            parent::fourOhThree($f3);
        }


        $web = \Web::instance();
        $web->send($file->getPath(), $file->getMimeType(), 512, false, $file->getOriginalFilename());
        $out=ob_get_clean();
    }
}