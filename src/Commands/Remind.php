<?php
/**
 * Remind command.
 * "Remind yourself that overconfidence is a slow and insidious killer."
 */

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command as Command;
use Ancestor\CommandHandler\CommandHandler as CommandHandler;

Class Remind extends Command {
    private $response = '***Remind yourself that overconfidence is a slow and insidious killer.***';

    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'remind', 'Teaches you or a *[@user]* the important lesson about life.');
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        if (empty($args)) {
            $message->channel->send($this->response);
        }
        foreach ($args as $arg) {

            if (preg_match(\CharlotteDunois\Yasmin\Models\MessageMentions::PATTERN_USERS, $arg) === 1 ||
                preg_match(\CharlotteDunois\Yasmin\Models\MessageMentions::PATTERN_ROLES, $arg) === 1) {
                $message->channel->send('***Remind yourself, ' . $arg
                    . ' , that overconfidence is a slow and insidious killer.***');
                return;
            }
        }
    }
}