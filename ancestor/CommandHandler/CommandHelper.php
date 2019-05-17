<?php

namespace Ancestor\CommandHandler;

use CharlotteDunois\Yasmin\Interfaces\TextChannelInterface;
use CharlotteDunois\Yasmin\Models\Message as Message;

class CommandHelper {

    /**
     * @var Message
     */
    public $message;

    /**
     * CommandHelper constructor.
     * @param Message $message
     */
    public function __construct(Message $message) {
        $this->message = $message;
    }

    /**
     * Respond to the message with embed image with an option title.
     * @param string $embedImageUrl
     * @param string|null $embedTitle
     */
    public function RespondWithEmbedImage(string $embedImageUrl, string $embedTitle = null) {
        $embedResponse = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
        $embedResponse->setImage($embedImageUrl);
        if (isset($embedTitle)) {
            $embedResponse->setTitle($embedTitle);
        }
        $this->message->channel->send('', array('embed' => $embedResponse));
    }

    public function RespondWithAttachedFile($fileData, string $fileName) {
        //Had to double-array, due to bug in the Yasmin\DataHelpers spamming warnings when dealing with binary data (0.5.1)
        $this->message->channel->send('', array('files' => array(array('data' => $fileData, 'name' => $fileName))));
    }

    /**
     * Gets either a user's avatar URL from the command arguments or URL to a picture. If 'args' is empty, returns avatar of the author
     * @param array $args
     * @return string
     */
    public function ImageUrlFromCommandArgs(array $args): string {
        if (!empty($args)) {
            if (preg_match(\CharlotteDunois\Yasmin\Models\MessageMentions::PATTERN_USERS, $args[0]) === 1) {
                return $this->message->mentions->users->first()->getDisplayAvatarURL(null, 'png');
            }
            if (filter_var($args[0], FILTER_VALIDATE_URL) && $this->HasImageExtension($args[0])) {
                return $args[0];
            }
        }
        return $this->message->author->getDisplayAvatarURL(null, 'png');
    }

    /**
     * Checks if the last characters of a string represent an image file extension.
     * @param string $path
     * @return bool
     */
    public function HasImageExtension(string $path): bool {
        return in_array(pathinfo($path, PATHINFO_EXTENSION),
            ['jpg', 'png', 'bmp', 'tif', 'gif', 'jpeg', 'webp']);
    }

    /**
     * Checks if channel has 'NSFW' in the channel's name, or if channel is marked as NSFW
     * @param TextChannelInterface $channel
     * @return bool
     */
    public static function ChannelIsNSFW(TextChannelInterface $channel): bool {
        if ((!empty($channel->nsfw) && $channel->nsfw === true) ||
            (!empty($channel->name) && strpos(strtolower($channel->name), 'nsfw') !== false)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $str
     * @return bool
     */
    public static function StringContainsURLs(string $str) {
        foreach (explode(' ', str_replace(array("\r", "\n"), ' ', $str)) as $item) {
            if (filter_var($item, FILTER_VALIDATE_URL)) {
                return true;
            }
        }
        return false;
    }

    public static function ImageFromFileHandler($fileHandler){
        return imagecreatefromstring(fread($fileHandler, filesize(stream_get_meta_data($fileHandler)['uri'])));
    }


}