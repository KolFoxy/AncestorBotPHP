<?php
/**
 * Created by PhpStorm.
 * User: KolBrony
 * Date: 18.05.2019
 * Time: 19:36
 */

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;

class Suicide extends Command {

    private $ownerId;

    function __construct(CommandHandler $handler, $ownerId) {
        parent::__construct($handler, 'suicide', '');
        $this->ownerId = $ownerId;
        $this->hidden = true;
    }

    /**
     * Runs the command.
     * @throws \Throwable|\Exception|\Error
     * @param \CharlotteDunois\Yasmin\Models\Message $message
     * @param array $args
     */
    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        if ($message->author->id === $this->ownerId){
            throw new \Exception('Suicide exception by '.$message->author->username);
        }

    }
}