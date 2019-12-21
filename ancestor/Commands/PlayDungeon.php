<?php

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;

class PlayDungeon extends Command {

    const REQUIRED_CHANNEL_TOPIC = '[ancestor-bot-play]';

    public function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'dplay', 'Play and interact with a short dungeon mission.',
            ['play', 'playdd', 'dd', 'dp']);
    }

    public function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        // TODO: Implement run() method.
    }
}