<?php

namespace Ancestor\Command;

use Ancestor\BotIO\BotIoInterface;
use Ancestor\BotIO\ChannelInterface;
use Ancestor\BotIO\EmbedObject;
use Ancestor\BotIO\MessageInterface;

class CommandHelper {

    const MAX_IMAGE_SIZE = 2800 * 2800;

    /**
     * @var BotIoInterface
     */
    public BotIoInterface $client;

    /**
     * CommandHelper constructor.
     * @param BotIoInterface $client
     */
    public function __construct(BotIoInterface $client) {
        $this->client = $client;
    }

    /**
     * Gets either a user's avatar URL from the command arguments or URL to a picture. If 'args' is empty, returns avatar of the author
     * @param array $args
     * @param MessageInterface $message
     * @return string
     */
    public static function imageUrlFromCommandArgs(array $args, MessageInterface $message): string {
        if (!empty($args)) {
            if (self::checkIfStringContainsUserMention($args[0])) {
                return $message->getUserMentions()[0]->getAvatarUrl();
            }
            if (filter_var($args[0], FILTER_VALIDATE_URL) && self::hasImageExtension($args[0])) {
                return $args[0];
            }
        }
        return  $message->getAuthor()->getAvatarUrl();
    }

    /**
     * Checks if the last characters of a string represent an image file extension.
     * @param string $path
     * @return bool
     */
    public static function hasImageExtension(string $path): bool {
        return in_array(pathinfo($path, PATHINFO_EXTENSION),
            ['jpg', 'png', 'bmp', 'tif', 'gif', 'jpeg', 'webp']);
    }

    /**
     * @param string $str
     * @return bool
     */
    public static function stringContainsURLs(string $str) {
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
    public static function imageFromFileHandler($fileHandler) {
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
     * @param EmbedObject $mergeInto
     * @param EmbedObject|array $mergeFrom
     */
    public static function mergeEmbed(EmbedObject $mergeInto, $mergeFrom) {
        if (is_a($mergeFrom, EmbedObject::class)) {
            if ($mergeFrom->title != null && $mergeFrom->description != null) {
                $mergeInto->addField($mergeFrom->title, $mergeFrom->description);
            }
            $fields = $mergeFrom->fields;
        } else {
            $fields = $mergeFrom;
        }
        foreach ($fields as $field) {
            $mergeInto->addField($field['title'], $field['body'], $field['inline']);
        }
    }

    public static function checkIfStringContainsRole(string $input) : bool {
        //TODO: implement method
    }

    public static function checkIfStringContainsUserMention(string $input) : bool {
        //TODO: implement method
    }

    /**
     * @param string $title
     * @param string $body
     * @param bool $inline
     * @return array ['title' => string, 'body' => string, 'inline' => bool]
     */
    public static function getEmbedField(string $title, string $body, bool $inline = false) {
        return [
            'title' => $title,
            'body' => $body,
            'inline' => $inline,
        ];
    }


}