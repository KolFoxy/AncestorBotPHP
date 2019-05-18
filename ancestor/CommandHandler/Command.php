<?php

namespace Ancestor\CommandHandler;

abstract class Command {
    /** @var \CharlotteDunois\Yasmin\Client */
    protected $client;

    /** @var CommandHandler */
    protected $handler;

    /** @var string */
    public $path;

    /** @var array */
    public $aliases;

    /**
     * @var bool Whether or not the command should be seen by other commands, such as Help.
     */
    public $hidden = false;

    protected $name = null;

    protected $description = null;

    function __construct(CommandHandler $handler, string $name, string $description, array $aliases = null) {
        $this->client = $handler->client;
        $this->handler = $handler;
        $this->name = $name;
        $this->description = $description;
        if(!empty($aliases)){
            $this->aliases = $aliases;
        }
    }

    /**
     * Returns the command name
     * @return string
     * */
    function getName() : string {
        return $this->name;
    }

    /**
     * Returns the command description
     * @return string
     * */
    function getDescription() : string {
        return $this->description;
    }

    /**
     * Runs the command.
     * @throws \Throwable|\Exception|\Error
     * @param \CharlotteDunois\Yasmin\Models\Message $message
     * @param array $args
     */
    abstract function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args);
}