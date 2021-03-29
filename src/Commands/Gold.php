<?php

/**
 * "Give random reward" command
 */

namespace Ancestor\Commands;

use Ancestor\BotIO\BotIoInterface;
use Ancestor\BotIO\MessageInterface;
use Ancestor\Command\Command as Command;
use Ancestor\Command\CommandHandler as CommandHandler;
use Ancestor\Command\CommandHelper;
use Ancestor\RandomData\RandomDataProvider as RandomDataProvider;

class Gold extends Command {

    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'gold', 'Gives a random reward.');
    }

    function run(MessageInterface $input, array $args) {
        $rdp = RandomDataProvider::getInstance();
        $input->replyWithEmbedImage('',$rdp->getRandomRewardQuote(),$rdp->getRandomReward());

    }
}