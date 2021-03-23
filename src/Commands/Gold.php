<?php

/**
 * "Give random reward" command
 */

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command as Command;
use Ancestor\CommandHandler\CommandHandler as CommandHandler;
use Ancestor\RandomData\RandomDataProvider as RandomDataProvider;

class Gold extends Command {

    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'gold', 'Gives a random reward.');
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        $RDP = RandomDataProvider::GetInstance();
        $CH = new \Ancestor\CommandHandler\CommandHelper($message);
        $CH->RespondWithEmbedImage($RDP->GetRandomReward(), $RDP->GetRandomRewardQuote());
    }
}