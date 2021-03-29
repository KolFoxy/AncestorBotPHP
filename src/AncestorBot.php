<?php

namespace Ancestor;

use Ancestor\AncestorTraits\CheckResolveTrait;
use Ancestor\AncestorTraits\NsfwResponseTrait;
use Ancestor\BotIO\BotIoInterface;
use Ancestor\BotIO\MessageInterface;
use Ancestor\Command\CommandHandler;
use Ancestor\Commands\Fight;
use Ancestor\Commands\Gold;
use Ancestor\Commands\Read;
use Ancestor\Commands\Remind;
use Ancestor\Commands\Reveal;
use Ancestor\Commands\Roll;
use Ancestor\Commands\Spin;
use Ancestor\Commands\Stress;

use Ancestor\Commands\Zalgo;
use Discord\Discord;
use Throwable;

class AncestorBot {

    use CheckResolveTrait;
    use NsfwResponseTrait;

    /**
     * @var BotIoInterface
     */
    private BotIoInterface $client;

    /**
     * @var array
     */
    private array $config;

    /**
     * Url to the default "spin" response.
     */
    const ARG_TIDE_URL = 'tideURL';
    /**
     * Url to the default "stress" response.
     */
    const ARG_STRESS_URL = 'stressURL';
    /**
     * Commands prefix.
     */
    const ARG_PREFIX = 'prefix';
    /**
     * Chance of NSFW response.
     */
    const ARG_NSFW_CHANCE = 'NSFWchance';

    const ARG_OWNER_ID = 'ownerId';

    /**
     * @var CommandHandler
     */
    private CommandHandler $commandHandler;

    public function __construct(BotIoInterface $client, array $config, CommandHandler $commandHandler = null) {
        $this->client = $client;
        $this->config = $config;
        if ($commandHandler != null) {
            $this->commandHandler = $commandHandler;
        } else {

            $this->commandHandler = new CommandHandler($client, $config[self::ARG_PREFIX]);
            $this->commandHandler->registerCommands($this->getDefaultCommands());
        }
        $this->setupClient();

    }

    /** @noinspection PhpUnhandledExceptionInspection */
    private function getDefaultCommands(): array {
        return [
            new Gold($this->commandHandler),
            new Remind($this->commandHandler),
            new Roll($this->commandHandler),
            new Spin($this->commandHandler, $this->config[self::ARG_TIDE_URL]),
            new Stress($this->commandHandler, $this->config[self::ARG_STRESS_URL]),
            new Zalgo($this->commandHandler),
            new Read($this->commandHandler),
            new Reveal($this->commandHandler),
            new Fight($this->commandHandler),
        ];
    }

    private function setupClient() {

        $discord = new Discord();

        $this->client->on('error', function (Throwable $error) {
            echo $error->getMessage();
        });

        $this->client->on('ready', function () {
            echo 'Successful login into ' . $this->client->getUser()->getTag() . PHP_EOL;
        });

        $this->client->on('message', function (MessageInterface $message) {
            if ($message->getAuthor()->isBot()) return;
            if ($this->commandHandler->handleMessage($message)) {
                return;
            }

            if ($this->nsfwResponse($message,$this->client,$this->config[self::ARG_NSFW_CHANCE])) {
                return;
            }

            $this->checkResolveResponse($message, $this->client);

        });
    }

    public function login(string $token) {
        $this->client->login($token);
    }

}