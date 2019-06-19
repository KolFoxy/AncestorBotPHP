<?php
/**
 * Created by PhpStorm.
 * User: KolBrony
 * Date: 19.06.2019
 * Time: 14:38
 */

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;

class Fight extends Command {

    public function __construct(CommandHandler $handler, string $name, string $description, array $aliases = null) {
        parent::__construct($handler, $name, $description, $aliases);
    }

    public function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        // TODO: Implement run() method.
    }
}