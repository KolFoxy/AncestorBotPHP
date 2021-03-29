<?php

namespace Ancestor\Commands;

use Ancestor\BotIO\MessageInterface;
use Ancestor\Command\Command as Command;
use Ancestor\Command\CommandHandler as CommandHandler;

class Roll extends Command {
    private string $title = '***The dice strikes the ground!***';

    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'roll', '``roll`` or ``roll [MIN] [MAX]`` or ``roll [MAX]`` - rolls a random integer (default ``roll`` is from 1 to 6)');
    }

    function run(MessageInterface $message, array $args) {
        $footerImage = $this->handler->client->getUser()->getAvatarUrl();
        $footerText = $this->handler->client->getUser()->getUsername();
        $result = null;
        $argsLen = count($args);
        if ($argsLen === 1 && ctype_digit($args[0])) {
            $result = mt_rand(1, intval($args[0]));
        } elseif ($argsLen >= 2 && ctype_digit($args[0] . $args[1])) {
            $first = intval($args[0]);
            $second = intval($args[1]);
            if ($second >= $first) {
                $result = mt_rand(intval($args[0]), intval($args[1]));
            }
        } else {
            $result = mt_rand(1, 6);
        }
        if (!is_int($result)) {
            $message->reply('Invalid input');
            return;
        }
        $message->getChannel()->sendWithFootedEmbed('', $this->title, '🎲**' . $result . '**', $footerText, $footerImage);
    }
}