<?php

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;
use Ancestor\Curio\Curio;
use Ancestor\RandomData\RandomDataProvider;

class Read extends Command {

    /**
     * Array of writing curios.
     * @var Curio[]
     */
    private $curios;

    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'read',
            'You encounter a peace of writing! The consequences can be unforeseen...',
            ['book', 'fuckbooks', 'knowledge']);

        $mapper = new \JsonMapper();
        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . '/data/writings.json'));
        $mapper->bExceptionOnMissingData = true;
        $this->curios = $mapper->mapArray(
            $json, [], Curio::class
        );
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        $message->reply(RandomDataProvider::GetInstance()->GetRandomData($this->curios)->name);
    }
}