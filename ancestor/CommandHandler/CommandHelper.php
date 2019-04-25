<?php
/**
 * Created by PhpStorm.
 * User: KolBrony
 * Date: 25.04.2019
 * Time: 14:37
 */

namespace Ancestor\CommandHandler;

class CommandHelper {

    /**
     * Gets either a user's avatar URL from the command arguments or URL to a picture
     * @param array $args
     * @param \CharlotteDunois\Yasmin\Models\Message $message
     * @return string
    */
    public static function ImageUrlFromCommandArgs(array $args, \CharlotteDunois\Yasmin\Models\Message $message) : string {
        if (!empty($args)) {
            if (preg_match(\CharlotteDunois\Yasmin\Models\MessageMentions::PATTERN_USERS, $args[0]) === 1) {
                return $message->mentions->users->first()->getDisplayAvatarURL(null, 'png');
            }
            if (filter_var($args[0], FILTER_VALIDATE_URL) && self::HasImageExtension($args[0])) {
                return $args[0];
            }
        }
        return $message->author->getDisplayAvatarURL(null, 'png');
    }

    /**
     * Checks if the last characters of a string represent an image file extension.
     * @param string $path
     * @return bool
     */
    public static function HasImageExtension(string $path) : bool{
        return in_array(pathinfo($path,PATHINFO_EXTENSION),
            ['jpg', 'png', 'bmp', 'tif', 'gif', 'jpeg', 'webp']);
    }

    /** Returns either a resource image or FALSE if image is unavailable or unsupported.
     * @param string $url
     * @return bool|resource
     */
    public static function ImageFromURL(string $url) {
        $file = file_get_contents($url);
        if ($file === false) {
            return $file;
        }
        return imagecreatefromstring($file);
    }





}