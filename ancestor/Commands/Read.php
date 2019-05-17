<?php

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;
use Ancestor\Curio\Action;
use Ancestor\Curio\Curio;
use const Ancestor\Curio\DEFAULT_EMBED_COLOR;
use Ancestor\Curio\Effect;
use Ancestor\FileDownloader\FileDownloader;
use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use Ratchet\RFC6455\Messaging\Message;

class Read extends Command {

    /**
     * Array of writing curios.
     * @var Curio[]
     */
    private $curios;

    /**
     * @var Effect
     */
    private $defaultEffect;

    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'read',
            'You encounter a peace of writing! The consequences can be unforeseen...',
            ['book', 'fuckbooks', 'knowledge']);

        $mapper = new \JsonMapper();
        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . '/data/writings.json'));
        $mapper->bExceptionOnMissingData = true;
        $this->curios = $mapper->mapArray(
            $json, [], Curio::class
        );

        $this->defaultEffect = new Effect();
        $this->defaultEffect->name = "Nothing happened.";
        $this->defaultEffect->description = "You choose to walk away in peace.";
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        $curio = $this->curios[mt_rand(0, sizeof($this->curios) - 1)];
        $message->reply('', ['embed' => $curio->getEmbedResponse($this->handler->prefix . $this->name),
            "files" => ['path' => 'http://i.imgur.com/hXp7LRI.png']]);
    }

    function performEffect(Message $message, Effect $effect): MessageEmbed {
        $messageEmbed = new MessageEmbed();
        $messageEmbed->setColor(DEFAULT_EMBED_COLOR);
        $messageEmbed->setTitle('***' . $effect->name . '***');
        $messageEmbed->setDescription($effect->description);
        return $messageEmbed;
    }

    function addAvatarToTemplate($avatarFileHandler, string $templatePath, ImageTemplate $template) {

    }

}