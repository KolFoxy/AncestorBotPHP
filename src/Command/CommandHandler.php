<?php

namespace Ancestor\Command;

use Ancestor\BotIO\BotIoInterface;
use Ancestor\BotIO\MessageInterface;

class CommandHandler {
    protected static string $FAILED_RESPONSE = "The abyss has finally got me, for I am unable to process your request. Or perhaps the futile experiments of my creators have finally shown the whole scope of their puniness.";
    protected static string $HELP_COMMAND = 'help';

    public BotIoInterface $client;

    /**
     * @var Command[]
     */
    protected array $commands;

    /** @var string */
    public string $prefix;

    /**
     * Constructor.
     * @param BotIoInterface $client
     * @param string $prefix
     */
    function __construct(BotIoInterface $client, string $prefix) {
        $this->client = $client;
        $this->prefix = $prefix;
        $this->commands = [];
    }


    /**
     * Message handler. Returns True if message was handled.
     * @param MessageInterface $message
     * @return bool
     */
    function handleMessage(MessageInterface $message): bool {
        if ($message->isAuthorBot()) {
            return false;
        }
        $prefixLen = mb_strlen($this->prefix);
        if (mb_substr($message->getContent(), 0, $prefixLen) !== $this->prefix) {
            return false;
        }

        $args = explode(' ', mb_substr($message->getContent(), $prefixLen));
        $commandName = mb_strtolower(array_shift($args));
        if (!array_key_exists($commandName,$this->commands)) {
            if ($commandName === self::$HELP_COMMAND) {
                $this->helpCommand($message, $args);
                return true;
            }
            return false;
        }
        try {
            $this->commands[$commandName]->run($message, $args);
        } catch (\Throwable $e) {
            $message->reply(self::$FAILED_RESPONSE . PHP_EOL . "Message: " . $e->getMessage());
            throw new \RuntimeException("Error with command->run: " . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        return true;
    }


    /**
     * Provides generic "help" command.
     * Can be overrode by creating specific "help" command
     * @param MessageInterface $message
     * @param array $args
     */
    function helpCommand(MessageInterface $message, array $args) {
        $answer = 'No such command';
        $title = 'Error';
        if (empty($args)) {
            $title = '**Commands. Use ' . $this->prefix . 'help [COMMAND] for more info.**';
            $commandsArray = array();
            foreach ($this->commands as $item) {
                if ($item->hidden) {
                    continue;
                }
                $commandsArray[$item->getName()] = '``' . $this->prefix . $item->getName() . '``' . PHP_EOL;
            }
            $answer = implode($commandsArray);
        } elseif (array_key_exists(mb_strtolower($args[0]), $this->commands)) {
            $commandName = mb_strtolower($args[0]);
            $comm = $this->commands[$commandName];
            if ($comm->hidden) {
                return;
            }
            $title = $this->prefix . $commandName;
            $answer = $comm->getDescription();
            if (!empty($comm->aliases)) {
                $answer .= PHP_EOL . 'Aliases: ' . implode(', ', $comm->aliases);
            }
        } elseif (mb_strtolower($args[0]) === 'help') {
            $title = $this->prefix . 'help';
            $answer = 'Use "' . $this->prefix . 'help [COMMAND]" to get command`s description';
        }
        $message->getChannel()->sendWithSimpleEmbed('', $title, $answer);
    }

    /**
     * Register a command.
     * @param Command $command
     */
    function registerCommand(Command $command) {
        try {
            $this->commands[mb_strtolower($command->getName())] = $command;
            if (!empty($command->aliases)) {
                foreach ($command->aliases as $alias) {
                    $this->commands[mb_strtolower($alias)] = $command;
                }
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Unable to load a command. Error: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /**
     * Register multiple commands.
     * @param Command[] $commands
     */
    function registerCommands(array $commands) {
        foreach ($commands as $command) {
            $this->registerCommand($command);
        }
    }
}