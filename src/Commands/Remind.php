<?php
/**
 * Remind command.
 * "Remind yourself that overconfidence is a slow and insidious killer."
 */

namespace Ancestor\Commands;

use Ancestor\BotIO\BotIoInterface;
use Ancestor\BotIO\MessageInterface;
use Ancestor\Command\Command as Command;
use Ancestor\Command\CommandHandler as CommandHandler;
use Ancestor\Command\CommandHelper;

class Remind extends Command {
    private $response = '***Remind yourself that overconfidence is a slow and insidious killer.***';

    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'remind', 'Teaches you or a *[@user]* the important lesson about life.');
    }

    function run(MessageInterface $message, array $args) {
        if (empty($args)) {
            $message->reply($this->response);
        }
        foreach ($args as $arg) {

            if (CommandHelper::checkIfStringContainsUserMention($arg) || CommandHelper::checkIfStringContainsRole($arg) === 1) {
                $message->getChannel()->send('***Remind yourself, ' . $arg
                    . ' , that overconfidence is a slow and insidious killer.***', null);
                return;
            }
        }
    }
}