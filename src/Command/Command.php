<?php

namespace Ancestor\Command;
use Ancestor\BotIO\MessageInterface;

abstract class Command {

    /** @var CommandHandler */
    protected CommandHandler $handler;

    /** @var string[] */
    public array $aliases;

    /**
     * @var bool Whether or not the command should be seen by other commands, such as Help.
     */
    public bool $hidden = false;

    protected ?string $name = null;

    protected ?string $description = null;

    function __construct(CommandHandler $handler, string $name, string $description, array $aliases = null) {
        $this->handler = $handler;
        $this->name = $name;
        $this->description = $description;
        if (!empty($aliases)) {
            $this->aliases = $aliases;
        }
    }

    /**
     * Returns the command name
     * @return string
     * */
    function getName(): string {
        return $this->name;
    }

    /**
     * Returns the command description
     * @return string
     * */
    function getDescription(): string {
        return $this->description;
    }

    /**
     * Runs the command.
     * @param MessageInterface $input
     * @param array $args
     * @throws \Throwable
     * @throws \Exception
     * @throws \Error
     */
    abstract function run(MessageInterface $input, array $args);

    /**
     * Returns prefixed command name.
     * @return string
     */
    public function getPrefixedName(): string {
        return $this->handler->prefix . $this->name;
    }

}