<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Ancestor\BotIO\DiscordPhpBot;

use Ancestor\BotIO\ChannelInterface;
use Ancestor\BotIO\EmbedInterface;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Embed\Embed;

class DiscordPhpChannel implements ChannelInterface {

    protected Channel $discordChannel;
    protected Discord $discord;

    public function __construct(Channel $discordChannel, Discord $discord) {
        $this->discordChannel = $discordChannel;
        $this->discord = $discord;
    }

    public function send(string $text, EmbedInterface $embed = null) {
        $de = null;
        if ($embed !== null) {
            DiscordPhpHelper::embedInterfaceToDiscordEmbed($embed, $this->discord);
        }
        $this->discordChannel->sendMessage($text, false, $de);
    }

    public function sendWithSimpleEmbed(string $text, string $embedTitle, string $embedBody) {
        $embed = new Embed($this->discord);
        $embed->description = $embedBody;
        $embed->title = $embedTitle;
        $this->discordChannel->sendMessage($text, false, $embed);
    }

    public function sendWithFootedEmbed(string $text, string $embedTitle, string $embedBody, string $footerText, ?string $footerImage) {
        $embed = new Embed($this->discord);
        $embed->description = $embedBody;
        $embed->title = $embedTitle;
        $embed->setFooter($footerText, $footerImage);
        $this->discordChannel->sendMessage($text, false, $embed);
    }

    public function sendWithFile(string $text, ?string $fileName, $file, EmbedInterface $embed = null) {
        $temp = tmpfile();
        fwrite($temp, $file);
        fseek($temp, 0);
        $this->discordChannel->sendFile(stream_get_meta_data($temp)['uri'], $fileName, $text)
            ->done(function () use ($temp) {
                fclose($temp);
            });

        if ($embed !== null) {
            $this->discordChannel->sendEmbed(DiscordPhpHelper::embedInterfaceToDiscordEmbed($embed, $this->discord));
        }
    }

    public function isNSFW(): bool {
        return $this->discordChannel->nsfw || (mb_strpos($this->discordChannel->name, 'nsfw') !== false);
    }

    public function getName(): string {
        return $this->discordChannel->name;
    }

    public function getId(): string {
        return $this->discordChannel->id;
    }
}