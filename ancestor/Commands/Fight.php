<?php

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;
use Ancestor\CommandHandler\CommandHelper as Helper;
use Ancestor\CommandHandler\ReactionHandlerInterface;
use Ancestor\CommandHandler\TimedCommandManager;
use Ancestor\Interaction\Fight\FightManager;
use Ancestor\Interaction\Fight\EncounterCollectionInterface;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\HeroClass;
use Ancestor\Interaction\MonsterType;
use CharlotteDunois\Yasmin\Interfaces\DMChannelInterface;
use CharlotteDunois\Yasmin\Interfaces\TextChannelInterface;
use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\MessageReaction;
use CharlotteDunois\Yasmin\Models\User;

class Fight extends Command implements EncounterCollectionInterface, ReactionHandlerInterface {
    const CHANNEL_SWITCH_REMINDER = 'Remember to switch to the original channel of the fight before continuing.';
    const TIMEOUT = 300.0;
    const SURRENDER_COMMAND = 'ff';
    const CHAR_INFO_COMMAND = 'stats';
    const CHAR_ACTIONS_COMMAND = 'actions';

    /**
     * @var HeroClass[]
     */
    private $classes = [];

    /**
     * @var TimedCommandManager
     */
    private $manager;

    /**
     * @var MonsterType[]
     */
    private $regMonsterTypes = [];

    /**
     * @var MonsterType[]
     */
    private $eliteMonsterTypes = [];

    /**
     * @var int
     */
    private $regularsMaxIndex;

    /**
     * @var int
     */
    private $classesMaxIndex;

    /**
     * @var int
     */
    private $elitesMaxIndex;


    const ABORT_MESSAGE = 'is now forever lost in space and time.';

    public function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'fight', 'Fight a random monster or type ``'
            . $handler->prefix
            . 'f single`` in order to start in single mode, in which only one monster will spawn.'
            . PHP_EOL . 'typing ``' . $handler->prefix . 'f ' . self::CHAR_INFO_COMMAND . '`` while fighting will show all of your character\'s stats'
            . PHP_EOL . 'typing ``' . $handler->prefix . 'f ' . self::CHAR_ACTIONS_COMMAND . '`` while fighting will show descriptions of all of your character\'s actions'
            . PHP_EOL . 'typing  ``' . $handler->prefix . 'f ' . self::SURRENDER_COMMAND . '`` while fighting will cancel the fight'
            . PHP_EOL . '``' . $handler->prefix . 'f test-[CLASS_NAME]`` will start a fight with selected class.'
            , ['f', 'df', 'dfight']);
        $this->manager = new TimedCommandManager($this->client);

        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;

        foreach (glob(dirname(__DIR__, 2) . '/data/heroes/*.json') as $path) {
            $json = json_decode(file_get_contents($path));
            try {
                $this->classes[] = $mapper->map($json, new HeroClass());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage() . ' IN PATH="' . $path . '"');
            }
        }
        foreach (glob(dirname(__DIR__, 2) . '/data/monsters/farmstead/*.json') as $path) {
            $json = json_decode(file_get_contents($path));
            try {
                $this->regMonsterTypes[] = $mapper->map($json, new MonsterType());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage() . ' IN PATH="' . $path . '"');
            }
        }
        foreach (glob(dirname(__DIR__, 2) . '/data/monsters/farmstead/elite/*.json') as $path) {
            $json = json_decode(file_get_contents($path));
            try {
                $this->eliteMonsterTypes[] = $mapper->map($json, new MonsterType());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage() . ' IN PATH="' . $path . '"');
            }
        }
        $this->classesMaxIndex = count($this->classes) - 1;
        $this->regularsMaxIndex = count($this->regMonsterTypes) - 1;
        $this->elitesMaxIndex = count($this->eliteMonsterTypes) - 1;
    }

    public function handleReaction(MessageReaction $reaction, User $user): bool {
        $fight = $this->getFightFromReaction($reaction, $user);
        if ($fight === null || $fight->lastFightMessageId !== $reaction->message->id) {
            return false;
        }
        $this->manager->refreshTimer($this->manager->generateId($reaction->message->channel->getId(), $user->id), self::TIMEOUT);
        $this->processFightAction($fight, Helper::emojiToNumber($reaction->emoji->name), $reaction->message->channel, $user->getAvatarURL(), $user->id);
        return true;
    }

    public function run(Message $message, array $args) {
        if (isset($args[0]) && $args[0] === 'help') {
            $this->handler->helpCommand($message, [$this->name]);
            return;
        }
        if (!$this->manager->userIsInteracting($message)) {
            $endless = true;
            $heroClassName = '';
            $this->processInitialArgs($args, $endless, $heroClassName);
            $hero = $this->createHero($message->author->username, $heroClassName);
            $fightManager = new FightManager($hero, $this, $this->handler->prefix . 'f', $endless);
            $message->reply('', ['embed' => $fightManager->start()]);
            $this->manager->addInteraction($message, self::TIMEOUT, $fightManager,
                function () use ($fightManager, $message) {
                    $this->sendEndscreen($message->channel, $fightManager, $message->author->getAvatarURL(), $message->author->__toString());
                }
            );
            return;
        }
        $this->processActiveUserInput($message, $args);
    }

    public function createHero(string $heroName, string $heroClassName): Hero {
        $heroClass = null;
        if ($heroClassName !== '') {
            foreach ($this->classes as $class) {
                if (mb_strtolower($class->name) === $heroClassName) {
                    $heroClass = $class;
                }
            }
        }
        if ($heroClass === null) {
            $heroClass = $this->randHeroClass();
        }
        return new Hero($heroClass, $heroName);
    }

    public function randHeroClass(): HeroClass {
        return $this->classes[mt_rand(0, $this->classesMaxIndex)];
    }

    public function processActiveUserInput(Message $message, array $args) {
        if (empty($args)) {
            return;
        }
        $actionName = implode(' ', $args);
        $fight = $this->getFightFromMessage($message);
        if ($actionName === self::SURRENDER_COMMAND) {
            $this->sendEndscreen($message->channel, $fight, $message->author->getAvatarURL(), $message->author->__toString());
            $this->manager->deleteInteraction($message);
            return;
        }
        if ($actionName === 'endscreen') { //for testing only, delete later
            $fight->killCount = 56;
            $fight->killedMonsters = [];
            for ($i = 0; $i < $fight->killCount; $i++) {
                if (mt_rand(0, 9) === 1) {
                    $fight->killedMonsters[] = $this->randHeroClass()->name;
                    continue;
                }
                $fight->killedMonsters[] = $this->randRegularMonsterType()->name;
            }
            $fight->createEndscreen($message->author->getAvatarURL(), $this->handler->client->getLoop())->done(
                function ($data) use ($message) {
                    $message->reply('', ['files' => [['data' => $data, 'name' => 'end.png']]]);
                },
                function () use ($message, $fight) {
                    $message->reply('**' . $fight->hero->name . '** ' . self::ABORT_MESSAGE);
                }
            );
            return;
        }
        $this->manager->refreshTimer($message, self::TIMEOUT);
        if ($actionName === self::CHAR_INFO_COMMAND) {
            $message->author->createDM()->done(function (DMChannelInterface $channel) use ($fight) {
                $channel->send('', ['embed' => $fight->getHeroStats()->setFooter(self::CHANNEL_SWITCH_REMINDER)]);
            });
            $message->reply('Check DMs for your hero\'s stats.');
            return;
        }
        if ($actionName === self::CHAR_ACTIONS_COMMAND) {
            $message->author->createDM()->done(function (DMChannelInterface $channel) use ($fight) {
                $channel->send('', ['embed' => $fight->getHeroActionsDescriptions()->setFooter(self::CHANNEL_SWITCH_REMINDER)]);
            });
            $message->reply('Check DMs for the list of actions and their descriptions.');
            return;
        }
        $this->processFightAction($fight, $actionName, $message->channel, $message->author->getAvatarURL(), $message->author->id);
    }

    /**
     * @param FightManager $fight
     * @param string|int|null $actionName
     * @param TextChannelInterface $channel
     * @param string $avatarUrl
     * @param string $userId
     */
    function processFightAction(FightManager $fight, $actionName, TextChannelInterface $channel, string $avatarUrl, string $userId) {
        $userMention = '<@' . $userId . '>';
        if (($action = $fight->getActionIfValid($actionName)) === null) {
            $channel->send($userMention . ' Invalid action.');
            return;
        }
        $fight->lastFightMessageId = '';
        $fight->createTurnPromise($action, $avatarUrl, $this->handler->client->getLoop())
            ->then(function ($messageData) use ($channel, $userMention) {
                return $channel->send($userMention, $messageData);
            }, function ($messageData) use ($channel, $userMention, $userId) {
                $this->manager->deleteInteraction($this->manager->generateId($channel->getId(), $userId));
                $channel->send($userMention, $messageData);
            })
            ->then(function (Message $sentMessage) use ($fight) {
                $fight->lastFightMessageId = $sentMessage->id;
                foreach ($fight->getCurrentReactionEmojis() as $emoji) {
                    $sentMessage->react($emoji);
                }
            });
    }

    function sendEndscreen(TextChannelInterface $channel, FightManager $fight, string $avatarUrl, string $mention) {
        if ($fight->killCount >= FightManager::ENDSCREEN_THRESHOLD) {
            $fight->createEndscreen($avatarUrl, $this->handler->client->getLoop())->done(
                function ($imageData) use ($channel, $fight, $mention) {
                    $channel->send($mention . ' **' . $fight->hero->name . '** ' . self::ABORT_MESSAGE
                        , ['files' => [['data' => $imageData, 'name' => 'end.png']]]);
                },
                function () use ($channel, $fight, $mention) {
                    $channel->send($mention . ' **' . $fight->hero->name . '** ' . self::ABORT_MESSAGE);
                }
            );
            return;
        }
        $channel->send($mention . ' **' . $fight->hero->name . '** ' . self::ABORT_MESSAGE);
    }

    public function processInitialArgs(array $args, bool &$endless, string &$heroClassName) {
        foreach ($args as $str) {
            if ($str === 'single') {
                $endless = false;
                continue;
            }
            if (mb_strpos($str, 'test-') === 0) {
                $heroClassName = str_replace('_', ' ', mb_strtolower(mb_substr($str, 5)));
                continue;
            }
        }
    }

    public function randRegularMonsterType(): MonsterType {
        return $this->regMonsterTypes[mt_rand(0, $this->regularsMaxIndex)];
    }

    public function randEliteMonsterType(): MonsterType {
        return $this->eliteMonsterTypes[mt_rand(0, $this->elitesMaxIndex)];
    }

    function getFightFromMessage(Message $message): ?FightManager {
        return $this->manager->getUserData($message);
    }

    function getFightFromReaction(MessageReaction $reaction, User $user): ?FightManager {
        return $this->manager->getUserData($this->manager->generateId($reaction->message->channel->getId(), $user->id));
    }
}
