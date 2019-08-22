<?php

namespace Ancestor;

use Ancestor\CommandHandler\CommandHandler;
use Ancestor\CommandHandler\CommandHelper;
use Ancestor\Commands\Fight;
use Ancestor\Commands\Gold;
use Ancestor\Commands\Read;
use Ancestor\Commands\Remind;
use Ancestor\Commands\Reveal;
use Ancestor\Commands\Roll;
use Ancestor\Commands\Spin;
use Ancestor\Commands\Stress;
use Ancestor\Commands\Suicide;
use Ancestor\Commands\TestReactions;
use Ancestor\Commands\Zalgo;
use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Client as Client;
use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use CharlotteDunois\Yasmin\Models\MessageReaction;
use CharlotteDunois\Yasmin\Models\User;

class AncestorBot {
    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $config;

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
     * @var \Ancestor\CommandHandler\CommandHandler
     */
    private $commandHandler = null;

    public function __construct(Client $client, array $config, CommandHandler $commandHandler = null) {
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
            new TestReactions($this->commandHandler),
        ];
    }

    private function setupClient() {
        $this->client->on('error', function (\Throwable $error) {
            echo $error->getMessage();
        });

        $this->client->on('ready', function () {
            echo 'Successful login into ' . $this->client->user->tag . PHP_EOL;
        });

        $this->client->on('message', function (Message $message) {
            if ($message->author->bot) return;
            if ($this->commandHandler->handleMessage($message)) {
                return;
            }

            $msgLowered = mb_strtolower($message->content);
            $index = mb_strpos($msgLowered, 'resolve is tested');
            if ($index !== false) {
                $this->checkResolveResponse($message, $index, $msgLowered);
                return;
            }

            if (CommandHelper::ChannelIsNSFW($message->channel) && mt_rand(1, 100) <= $this->config[self::ARG_NSFW_CHANCE]) {
                $this->nsfwResponse($message);
            }
        });

        $this->client->on('messageReactionAdd', function (MessageReaction $reaction, User $user) {
            if ($user->bot) {
                return;
            }
            $this->commandHandler->handleReaction($reaction, $user);
        });

    }

    private function checkResolveResponse(Message $message, int $index, string $msgLowered) {
        $response = RandomDataProvider::GetInstance()->GetRandomResolve();
        $embedResponse = new MessageEmbed();
        $embedResponse->setFooter($message->client->user->username, $message->client->user->getAvatarURL());
        $embedResponse->setDescription('***' . $response['quote'] . '***');
        if ($index != 0) {
            if (!empty($message->mentions->users) && count($message->mentions->users) > 0) {
                $message->channel->send('**' . '<@' . $message->mentions->users->last()->id . '>' .
                    ' is ' . $response['name'] . '**', ['embed' => $embedResponse]);
                return;
            }
            $index = strpos($msgLowered, 'my resolve is tested');
            if ($index !== false) {
                $message->channel->send('**' . '<@' . $message->author->id . '>' .
                    ' is ' . $response['name'] . '**', ['embed' => $embedResponse]);
                return;
            }
        }
        $message->channel->send('**' . $response['name'] . '**', ['embed' => $embedResponse]);
    }

    private function nsfwResponse(Message $message) {
        if ((!empty($message->attachments) && count($message->attachments) > 0) ||
            (!empty($message->embeds) && count($message->embeds) > 0) || CommandHelper::StringContainsURLs($message->content)) {
            $embedResponse = new MessageEmbed();
            $embedResponse->setFooter($message->client->user->username, $message->client->user->getAvatarURL());
            $embedResponse->setDescription(RandomDataProvider::GetInstance()->GetRandomNSFWQuote());
            $message->channel->send('', ['embed' => $embedResponse]);
        }
    }

    public function login(string $token) {
        $this->client->login($token);
    }

}