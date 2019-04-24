<?php
/**
 * Remind command.
 * "Remind yourself that overconfidence is a slow and insidious killer."
 */

return (
new class($handler) extends Ancestor\CommandHandler\Command {
    private $response = '***Remind yourself that overconfidence is a slow and insidious killer.***';

    function __construct(Ancestor\CommandHandler\CommandHandler $handler) {
        parent::__construct($handler, 'remind', 'teaches an individual the important lesson about life');
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args): void {
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
);