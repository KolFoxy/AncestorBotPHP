<?php

namespace Ancestor;

use Ancestor\AncestorTraits\CheckResolveTrait;
use Ancestor\AncestorTraits\NsfwResponseTrait;
use Ancestor\BotIO\BotIoInterface;
use Ancestor\BotIO\DiscordPhpBot\DiscordPhpClient;
use Ancestor\BotIO\DiscordPhpBot\DiscordPhpMessage;
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
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\HeroClass;
use Ancestor\Interaction\Stats\LightingEffectFactory;
use Ancestor\Interaction\Stats\StressStateFactory;
use Ancestor\Interaction\Stats\TrinketFactory;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Throwable;

class AncestorBot {

    use CheckResolveTrait;
    use NsfwResponseTrait;

    /**
     * @var Discord
     */
    private Discord $discord;

    private DiscordPhpClient $client;

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

    public function __construct(Discord $discord, array $config) {
        $this->discord = $discord;
        $this->config = $config;
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

        $this->discord->on('ready', function () {
            echo 'Successful login into ' . $this->discord->user->username . '#' . $this->discord->user->discriminator . PHP_EOL;

            $this->client = new DiscordPhpClient($this->discord);
            $this->commandHandler = new CommandHandler($this->client, $this->config[self::ARG_PREFIX]);
            $this->commandHandler->registerCommands($this->getDefaultCommands());

            $this->discord->on(Event::MESSAGE_CREATE, function (Message $discordMessage) {



                $message = new DiscordPhpMessage($discordMessage, $this->discord);
                if ($message->getAuthor()->isBot()) return;
                if ($this->commandHandler->handleMessage($message)) {
                    return;
                }

                if ($this->nsfwResponse($message, $this->client, $this->config[self::ARG_NSFW_CHANCE])) {
                    return;
                }

                $this->checkResolveResponse($message, $this->client);

            });
        });

    }

}