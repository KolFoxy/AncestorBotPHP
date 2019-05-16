<?php
/**
 * Created by PhpStorm.
 * User: KolBrony
 * Date: 16.05.2019
 * Time: 12:31
 */

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;

class Read extends Command {
    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'read',
            'You encounter a peace of writing! The consequences can be unforeseen...',
            ['book', 'fuckbooks']);
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        // TODO: Implement run() method.
    }
}