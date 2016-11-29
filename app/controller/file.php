<?php

namespace Controller;

use Util\Arr;

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

        $path = $file->getPath();

        $width = Arr::get($f3->get('GET'), 'w');
        $crop = Arr::get($f3->get('GET'), 'c', false);

        if ($file->isImage() && $width) {
            $path = self::resizeImageFile($file, $width, $crop);
        }

        $web = \Web::instance();
        $web->send($path, $file->getMimeType(), 512, false, $file->getOriginalFilename());
        $out=ob_get_clean();
    }

    /**
     * @param \Model\File $file
     * @param int $width
     * @param bool $crop
     * @return string
     */
    private static function resizeImageFile($file, $width, $crop = false)
    {
        // check if existing resized image exists
        $pathInfo = pathinfo($file->getPath());

        $resizeImagePath = "{$pathInfo['dirname']}/{$pathInfo['filename']}_resized{$width}.{$pathInfo['extension']}";

        if (file_exists($resizeImagePath)) {
            return $resizeImagePath;
        }

        $f3 = \Base::instance();
        $relativePath = "../../upload/{$pathInfo['filename']}.{$pathInfo['extension']}";
        $image = new \Image($relativePath);

        $image->resize($width, null, $crop, false);

        switch($file->getMimeType()) {
            case 'image/png':
            case 'image/x-png':
                $format = 'png';
                $quality = 8;
                break;

            case 'image/jpeg':
            case 'image/jpg':
                $format = 'jpeg';
                $quality = 100;
                break;

            default:
                $format = 'png';
                $quality = 8;
        }

        $f3->write($resizeImagePath, $image->dump($format, $quality));
        return $resizeImagePath;
    }
}