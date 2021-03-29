<?php

namespace Ancestor\Commands;

use Ancestor\BotIO\ChannelInterface;
use Ancestor\BotIO\MessageInterface;
use Ancestor\Command\Command;
use Ancestor\Command\CommandHandler;
use Ancestor\Command\TimedCommandManager;
use Ancestor\Interaction\Fight\FightManager;
use Ancestor\Interaction\Fight\EncounterCollectionInterface;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\HeroClass;
use Ancestor\Interaction\Incident\Incident;
use Ancestor\Interaction\Incident\IncidentCollection\IncidentCollection;
use Ancestor\Interaction\MonsterType;
use JsonMapper;

class Fight extends Command implements EncounterCollectionInterface {
    const CHANNEL_SWITCH_REMINDER = 'Remember to switch to the original channel of the fight before continuing.';
    const TIMEOUT = 600.0;
    const SURRENDER_COMMAND = 'ff';
    const CHAR_INFO_COMMAND = 'stats';
    const CHAR_ACTIONS_COMMAND = 'actions';

    /**
     * @var HeroClass[]
     */
    private array $classes = [];

    /**
     * @var TimedCommandManager
     */
    private TimedCommandManager $manager;

    /**
     * @var MonsterType[]
     */
    private array $regMonsterTypes = [];

    /**
     * @var MonsterType[]
     */
    private array $eliteMonsterTypes = [];

    /**
     * @var int
     */
    private int $regularsMaxIndex;

    /**
     * @var int
     */
    private int $classesMaxIndex;

    /**
     * @var int
     */
    private int $elitesMaxIndex;

    /**
     * @var IncidentCollection
     */
    private IncidentCollection $incidentCollection;


    const ABORT_MESSAGE = 'is now forever lost in space and time.';

    public function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'fight', 'Fight a random monster or type ``'
            . $handler->prefix
            . 'f single`` in order to start in single mode, in which only one monster will spawn.'
            . PHP_EOL . 'typing ``' . $handler->prefix . 'f ' . self::CHAR_INFO_COMMAND . '`` while fighting will show all of your character\'s stats'
            . PHP_EOL . 'typing ``' . $handler->prefix . 'f ' . self::CHAR_ACTIONS_COMMAND . '`` while fighting will show descriptions of all of your character\'s actions'
            . PHP_EOL . 'typing  ``' . $handler->prefix . 'f ' . self::SURRENDER_COMMAND . '`` while fighting will cancel the fight'
            . PHP_EOL . '``' . $handler->prefix . 'f play-[CLASS_NAME]`` will start a fight with selected class.'
            , ['f', 'df', 'dfight']);
        $this->manager = new TimedCommandManager($this->handler->client);

        $mapper = new JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $mapper->bExceptionOnUndefinedProperty = true;
        foreach (glob(dirname(__DIR__, 2) . '/data/heroes/*.json') as $path) {
            $json = json_decode(file_get_contents($path));
            try {
                $this->classes[] = $mapper->map($json, new HeroClass());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage() . ' IN PATH="' . $path . '"' . $e->getTraceAsString());
            }
        }
        foreach (glob(dirname(__DIR__, 2) . '/data/monsters/farmstead/*.json') as $path) {
            $json = json_decode(file_get_contents($path));
            try {
                $this->regMonsterTypes[] = $mapper->map($json, new MonsterType());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage() . ' IN PATH="' . $path . '"' . $e->getTraceAsString());
            }
        }
        foreach (glob(dirname(__DIR__, 2) . '/data/monsters/farmstead/elite/*.json') as $path) {
            $json = json_decode(file_get_contents($path));
            try {
                $this->eliteMonsterTypes[] = $mapper->map($json, new MonsterType());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage() . ' IN PATH="' . $path . '"' . $e->getTraceAsString());
            }
        }
        $this->classesMaxIndex = count($this->classes) - 1;
        $this->regularsMaxIndex = count($this->regMonsterTypes) - 1;
        $this->elitesMaxIndex = count($this->eliteMonsterTypes) - 1;
        $this->incidentCollection = IncidentCollection::getInstance();
    }

    public function run(MessageInterface $message, array $args) {
        if (isset($args[0]) && $args[0] === 'help') {
            $this->handler->helpCommand($message, [$this->name]);
            return;
        }
        if (!$this->manager->userIsInteracting($message)) {
            $endless = true;
            $heroClassName = '';
            $this->processInitialArgs($args, $endless, $heroClassName);
            $hero = $this->createHero($message->getAuthor()->getUsername(), $heroClassName);
            $fightManager = new FightManager($hero, $message->getAuthor()->getAvatarURL()
                , $this, $this->handler->prefix . 'f', $this->handler->client->getLoop(), $endless);
            $message->reply('', $fightManager->start());
            $this->manager->addInteraction($message, self::TIMEOUT, $fightManager, null,
                function () use ($fightManager, $message) {
                    $this->sendEndscreen($message->getChannel(), $fightManager, $message->getAuthor()->getMention());
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

    public function processActiveUserInput(MessageInterface $message, array $args) {
        if (empty($args)) {
            return;
        }
        $actionName = mb_strtolower(implode(' ', $args));
        $fight = $this->getFight($message);
        if ($actionName === self::SURRENDER_COMMAND) {
            $this->sendEndscreen($message->getChannel(), $fight, $message->getAuthor()->getMention());
            $this->manager->deleteInteraction($message);
            return;
        }
        $this->manager->refreshTimer($message, self::TIMEOUT);

        if ($actionName === self::CHAR_INFO_COMMAND) {
            $message->getAuthor()->createDM()->done(function (ChannelInterface $channel) use ($fight) {
                $channel->send('', $fight->getHeroStats()->setFooter(self::CHANNEL_SWITCH_REMINDER));
            });
            $message->reply('Check DMs for your hero\'s stats.');
            return;
        }
        if ($actionName === self::CHAR_ACTIONS_COMMAND) {
            $message->getAuthor()->createDM()->done(function (ChannelInterface $channel) use ($fight) {
                $channel->send('', $fight->getHeroActionsDescriptions()->setFooter(self::CHANNEL_SWITCH_REMINDER));
            });
            $message->reply('Check DMs for the list of actions and their descriptions.');
            return;
        }
        if (($action = $fight->getActionIfValid($actionName)) === null) {
            $message->reply('Invalid action.');
            return;
        }
        $fight->createTurnPromise($action)->done(
            function ($messageData) use ($message) {
                $message->reply('', $messageData);
            },
            function ($messageData) use ($message) {
                $this->manager->deleteInteraction($message);
                $message->reply('', $messageData);
            }
        );
    }

    function sendEndscreen(ChannelInterface $channel, FightManager $fight, string $mention) {
        if ($fight->killCount >= FightManager::ENDSCREEN_THRESHOLD) {
            $fight->createEndscreen()->done(
                function ($imageData) use ($channel, $fight, $mention) {
                    $channel->sendWithFile($mention . ' **' . $fight->hero->name . '** ' . self::ABORT_MESSAGE,
                        'end.png', $imageData);
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
            if (mb_strpos($str, 'play-') === 0) {
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

    public function randIncident(): Incident {
        return $this->incidentCollection->randIncident();
    }

    function getFight(MessageInterface $message): FightManager {
        return $this->manager->getUserData($message);
    }
}
