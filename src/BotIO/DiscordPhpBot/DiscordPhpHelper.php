<?php

namespace Ancestor\BotIO\DiscordPhpBot;

use Ancestor\BotIO\EmbedInterface;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Video;

class DiscordPhpHelper {
    public static function embedInterfaceToDiscordEmbed(EmbedInterface $embed, Discord $discord): Embed {
        $res = new Embed($discord);
        $res->title = $embed->getTitle();
        $res->description = $embed->getDescription();
        $res->color = $embed->getColor();
        $res->url = $embed->getUrl();
        $res->timestamp = $embed->getTimestamp();

        $res->setFooter($embed->getFooterText(), $embed->getFooterIconUrl());

        $res->setImage($embed->getImageUrl());

        $res->setThumbnail($embed->getThumbnailUrl());

        $res->setAuthor($embed->getAuthorName(), $embed->getAuthorIconUrl(), $embed->getAuthorUrl());

        $res->video = new Video($discord, ['url' => $embed->getVideoUrl(), 'height' => $embed->getVideoHeight(), 'width' => $embed->getVideoWidth()]);

        $fields = $embed->getFields();
        if ($fields !== null) {
            foreach ($fields as $field) {
                $res->addFieldValues($field['title'], $field['body'], $field['inline']);
            }
        }

        return $res;
    }

    public static function idToMention(string $id): string {
        return '<@' . $id . '>';
    }
}