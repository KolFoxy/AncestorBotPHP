<?php

namespace Ancestor\CommandHandler;

use CharlotteDunois\Yasmin\Models\MessageReaction;
use CharlotteDunois\Yasmin\Models\User;

class CommandHandler implements ReactionHandlerInterface {
    protected $FAILED_RESPONSE = "The abyss has finally got me, for I am unable to process your request. Or perhaps the futile experiments of my creators have finally shown the whole scope of their puniness.";
    protected $HELP_COMMAND = 'help';
    /** @var \CharlotteDunois\Yasmin\Client */
    public $client;

    /**
     * Holds all commands mapped by their name.
     * @var \CharlotteDunois\Collect\Collection()
     */
    protected $commands;

    /**
     * @var ReactionHandlerInterface[]
     */
    protected $reactionHandlers = [];

    /** @var string */
    public $prefix;

    /**
     * Constructor.
     * @param \CharlotteDunois\Yasmin\Client $client
     * @param string $prefix
     */
    function __construct(\CharlotteDunois\Yasmin\Client $client, string $prefix) {
        $this->client = $client;
        $this->prefix = $prefix;
        $this->commands = new \CharlotteDunois\Collect\Collection();
    }


    /**
     * Message handler. Returns True if message was handled.
     * @param \CharlotteDunois\Yasmin\Models\Message $message
     * @return bool
     */
    function handleMessage(\CharlotteDunois\Yasmin\Models\Message $message): bool {
        if ($message->author->bot) {
            return false;
        }
        $prefixLen = mb_strlen($this->prefix);
        if (mb_substr($message->content, 0, $prefixLen) !== $this->prefix) {
            return false;
        }

        $args = explode(' ', mb_substr($message->content, $prefixLen));
        $command = mb_strtolower(array_shift($args));
        if (!$this->commands->has($command)) {
            if ($command === $this->HELP_COMMAND) {
                $this->helpCommand($message, $args);
                return true;
            }
            return false;
        }
        try {
            $this->commands->get($command)->run($message, $args);
        } catch (\Throwable $e) {
            $message->reply($this->FAILED_RESPONSE . PHP_EOL . "Message: " . $e->getMessage());
            throw new \RuntimeException("Error with command->run: " . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        return true;
    }

    public function handleReaction(MessageReaction $reaction, User $user): bool {
        foreach ($this->reactionHandlers as $handler) {
            if ($handler->handleReaction($reaction, $user)) {
                return true;
            }
        }
        return false;
    }

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * Provides generic "help" command.
     * Can be overrode by creating specific "help" command
     * @param \CharlotteDunois\Yasmin\Models\Message $message
     * @param array $args
     */
    function helpCommand(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        $answer = 'No such command';
        $title = 'Error';
        if (empty($args)) {
            $title = '**Commands. Use ' . $this->prefix . 'help [COMMAND] for more info.**';
            $commandsArray = [];
            foreach ($this->commands->values() as $item) {
                if ($item->hidden) {
                    continue;
                }
                $commandsArray[$item->getName()] = '``' . $this->prefix . $item->getName() . '``' . PHP_EOL;
            }
            $answer = implode($commandsArray);
        } elseif ($this->commands->has(mb_strtolower($args[0]))) {
            $command = mb_strtolower($args[0]);
            $comm = $this->commands->get($command);
            if ($comm->hidden) {
                return;
            }
            $title = $this->prefix . $command;
            $answer = $comm->getDescription();
            if (!empty($comm->aliases)) {
                $answer .= PHP_EOL . 'Aliases: ' . implode(', ', $comm->aliases);
            }
        } elseif (mb_strtolower($args[0]) === 'help') {
            $title = $this->prefix . 'help';
            $answer = 'Use "' . $this->prefix . 'help [COMMAND]" to get command`s description';
        }
        /** @noinspection PhpUnhandledExceptionInspection */
        $embedResponse = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
        $embedResponse->addField($title, $answer);
        $message->channel->send('', ['embed' => $embedResponse]);
    }

    /**
     * Register a command.
     * @param Command|ReactionHandlerInterface $command
     */
    function registerCommand($command) {
        try {
            if ($command instanceof Command) {
                $this->commands->set(mb_strtolower($command->getName()), $command);
                if (!empty($command->aliases)) {
                    foreach ($command->aliases as $alias) {
                        $this->commands->set(mb_strtolower($alias), $command);
                    }
                }
            }
            if ($command instanceof ReactionHandlerInterface) {
                $this->reactionHandlers[] = $command;
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Unable to load a command. Error: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /**
     * Register multiple commands.
     * @param array $commands
     * @throws \RuntimeException
     */
    function registerCommands(array $commands) {
        foreach ($commands as $command) {
            $this->registerCommand($command);
        }
    }
}