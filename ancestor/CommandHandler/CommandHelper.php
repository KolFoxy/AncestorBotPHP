<?php

namespace Ancestor\CommandHandler;

use CharlotteDunois\Yasmin\Interfaces\TextChannelInterface;
use CharlotteDunois\Yasmin\Models\Message as Message;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class CommandHelper {

    const MAX_IMAGE_SIZE = 2800 * 2800;

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
        $this->message->channel->send('', ['embed' => $embedResponse]);
    }

    public function RespondWithAttachedFile($fileData, string $fileName, $embed = null, $content = '') {
        //Had to double-array, due to bug in the Yasmin\DataHelpers spamming warnings when dealing with binary data (0.5.1)
        $this->message->channel->send($content, ['files' => [['data' => $fileData, 'name' => $fileName]],
            'embed' => $embed]);
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
        foreach (explode(' ', str_replace(["\r", "\n"], ' ', $str)) as $item) {
            if (filter_var($item, FILTER_VALIDATE_URL)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $fileHandler
     * @return resource|false
     */
    public static function ImageFromFileHandler($fileHandler) {
        $imageSize = getimagesize(stream_get_meta_data($fileHandler)['uri']);

        if ($imageSize === false || $imageSize[0] * $imageSize[1] > self::MAX_IMAGE_SIZE) {
            return false;
        }
        return imagecreatefromstring(fread($fileHandler, filesize(stream_get_meta_data($fileHandler)['uri'])));
    }

    /**
     * @param string $str
     * @param int $length
     * @return string[]
     */
    public static function mb_str_split(string $str, int $length = 0): array {
        if ($length > 0) {
            $result = [];
            $strLength = mb_strlen($str);
            for ($i = 0; $i < $strLength; $i += $length) {
                $result[] = mb_substr($str, $i, $length);
            }
            return $result;
        }
        return [''];
    }

    /**
     * @param MessageEmbed $mergeInto
     * @param MessageEmbed|array $mergeFrom
     */
    public static function mergeEmbed(MessageEmbed $mergeInto, $mergeFrom) {
        if (is_a($mergeFrom, MessageEmbed::class)) {
            if ($mergeFrom->title != null && $mergeFrom->description != null) {
                $mergeInto->addField($mergeFrom->title, $mergeFrom->description);
            }
            $fields = $mergeFrom->fields;
        } else {
            $fields = $mergeFrom;
        }
        foreach ($fields as $field) {
            $mergeInto->addField($field['name'], $field['value'], $field['inline']);
        }
    }


}