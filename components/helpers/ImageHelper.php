<?php
namespace app\components\helpers;

use Yii;
use yii\helpers\FileHelper;
use yii\imagine\Image;
use Imagine\Image\ImageInterface;

class ImageHelper {

    /**
     * Most popular screen sizes from https://www.w3counter.com/globalstats.php
     * @var array
     */
    const STANDARD_SCREEN_WIDTHS = [
        1920, 1600, 1366, 1024, 640
    ];

    /**
     * Returns absolute path of web accessible folder
     * @param string $path
     * @return string
     */
    public static function getAbsolutePath($path) {
        if ($path[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $path) > 0)
            return $path;
        return Yii::getAlias('@webroot/' . trim($path, '/'));
    }

    /**
     * Resize and save image
     * @param string|resource|ImageInterface $image either ImageInterface, resource or a string containing file path
     * @param int $width Width to resize in pixels
     * @param string $folder Directory to save image to
     * @param string $fileName Image file name
     * @param boolean $rewriteIfExists Should method rewrite the existing file. Number suffix will be added to filename if false
     * @return string|bool File name of saved image or false in case of failure
     */
    public static function resizeAndSave($image, $width, $folder, $fileName, $rewriteIfExists = true) {
        try {
            $folder = static::getAbsolutePath($folder);
            if (!FileHelper::createDirectory($folder)) {
                Yii::error("Error happend while saving image. Failed to create folder `$folder`");
                return false;
            }

            if (!$rewriteIfExists && file_exists($folder . '/' . $fileName)) {
                $actualName = pathinfo($fileName, PATHINFO_FILENAME);
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $originalName = $actualName;

                $i = 1;
                do {
                    $actualName = (string) $originalName . '-' . $i;
                    $i++;
                } while (file_exists($folder . '/' . $actualName.'.'.$extension));
                $fileName = $actualName . '.' . $extension;
            }

            Image::resize($image, $width, null)->save($folder . '/' . $fileName);
            return $fileName;
        }
        catch (\Exception $e) {
            Yii::error('Error happend while saving image. ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resize image for standard screen sizes and save image
     * @param string|resource|ImageInterface $image either ImageInterface, resource or a string containing file path
     * @param string $folder Directory to save image to
     * @param string $fileName Image file name
     * @param boolean $rewriteIfExists Should method rewrite the existing file. Number suffix will be added to filename if false
     * @return string|bool File name of saved image or false in case of failure
     */
    public static function resizeToStandardsAndSave($image, $folder, $fileName, $rewriteIfExists = true) {
        $widths = static::STANDARD_SCREEN_WIDTHS;
        // first save the biggest resoution
        $width = array_shift($widths);
        $fileName = static::resizeAndSave($image, $width, $folder, $fileName, $rewriteIfExists);
        if (!$fileName) {
            return false;
        }

        // save other sizes
        foreach ($widths as $width) {
            static::resizeAndSave($image, $width, $folder . '/' . $width, $fileName, $rewriteIfExists);
        }

        return $fileName;
    }

    /**
     * Removes image
     * @param  string $file File path
     * @return boolean
     */
    public static function remove($file) {
        $file = static::getAbsolutePath($file);
        return FileHelper::unlink($file);
    }

    /**
     * Removes images in all standard widths
     * @param  string $folder
     * @param  string $fileName
     * @return bool
     */
    public function removeStandards($folder, $fileName) {
        $folder = static::getAbsolutePath($folder);
        if (!FileHelper::unlink($folder . '/' . $fileName)) {
            return false;
        }

        $widths = static::STANDARD_SCREEN_WIDTHS;
        for ($i = 1; $i < count($widths); $i++) {
            FileHelper::unlink($folder . '/' . $widths[$i] . '/' . $fileName);
        }
        return true;
    }

    /**
     * Removes folder
     * @param  string $folder
     * @param  bool $removeIfNotEmpty
     * @return bool
     */
    public function removeFolder($folder, $removeIfNotEmpty = false) {
        $folder = static::getAbsolutePath($folder);
        if (!$removeIfNotEmpty && count(scandir($folder)) > 2) {
            return false;
        }
        FileHelper::removeDirectory($folder);
        return true;
    }
}
