<?php

namespace Ancestor\BotIO\DiscordPhpBot;

use Ancestor\BotIO\EmbedInterface;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Video;

class DiscordPhpHelper {
    public static function embedInterfaceToDiscordEmbed(EmbedInterface $embed, Discord $discord): Embed {
        $res = new Embed($discord);

        if ($embed->getTitle() !== null)
        $res->title = $embed->getTitle();

        if ($embed->getDescription() !== null)
        $res->description = $embed->getDescription();

        if ($embed->getColor() !== null)
        $res->color = $embed->getColor();

        if ($embed->getUrl() !== null)
        $res->url = $embed->getUrl();

        if ($embed->getTimestamp() !== null)
        $res->timestamp = $embed->getTimestamp();

        if ($embed->getFooterText() !== null)
        $res->setFooter($embed->getFooterText(), $embed->getFooterIconUrl());

        if ($embed->getImageUrl() !== null)
        $res->setImage($embed->getImageUrl());

        if ($embed->getThumbnailUrl() !== null)
        $res->setThumbnail($embed->getThumbnailUrl());

        if ($embed->getAuthorName() !== null) {
            $res->setAuthor($embed->getAuthorName(), $embed->getAuthorIconUrl(), $embed->getAuthorUrl());
        }

        if ($embed->getVideoUrl() !== null)
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