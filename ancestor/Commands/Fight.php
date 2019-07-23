<?php

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;
use Ancestor\CommandHandler\CommandHelper;
use Ancestor\CommandHandler\TimedCommandManager;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\HeroClass;
use Ancestor\Interaction\Monster;
use Ancestor\Interaction\MonsterType;
use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use function GuzzleHttp\Psr7\str;

class Fight extends Command {

    const TIMEOUT = 300.0;
    const SURRENDER_COMMAND = 'ff';
    const CHAR_INFO_COMMAND = 'stats';
    const CHAR_ACTIONS_COMMAND = 'actions';

    /**
     * @var HeroClass[]
     */
    private $classes = [];

    /**
     * @var int
     */
    private $numOfClasses;

    /**
     * @var TimedCommandManager
     */
    private $manager;

    /**
     * @var MonsterType[]
     */
    private $monsterTypes = [];
    /**
     * @var int
     */
    private $numOfTypes;


    public function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'fight', 'Fight a random monster or type "'
            . $handler->prefix
            . 'f endless" in order to start in endless mode, in which more and more monsters will come after defeating previous ones!'
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
            $this->classes[] = $mapper->map($json, new HeroClass());
        }
        foreach (glob(dirname(__DIR__, 2) . '/data/monsters/*.json') as $path) {
            $json = json_decode(file_get_contents($path));
            $arrayOfMonsterTypes = $mapper->mapArray($json, [], MonsterType::class);
            $this->monsterTypes = array_merge($this->monsterTypes, $arrayOfMonsterTypes);
        }

        $this->numOfClasses = sizeof($this->classes) - 1;
        $this->numOfTypes = sizeof($this->monsterTypes) - 1;

    }

    public function run(Message $message, array $args) {
        if (!$this->manager->userIsInteracting($message)) {
            $endless = false;
            $heroClassName = '';
            $this->processInitialArgs($args, $endless, $heroClassName);
            $hero = $this->createHero($message->author->username, $heroClassName);
            $monster = $this->getRandomMonster();
            $heroFirst = (bool)mt_rand(0, 1);
            $message->reply('', ['embed' => $this->getEncounterEmbed($hero, $monster, $heroFirst)]);
            $this->manager->addInteraction($message, self::TIMEOUT, [$hero, $monster, $endless]);
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
            $heroClass = $this->classes[mt_rand(0, $this->numOfClasses)];
        }
        return new Hero($heroClass, $heroName);
    }

    public function processActiveUserInput(Message $message, array $args) {
        if (empty($args)) {
            return;
        }
        $actionName = implode(' ', $args);
        if ($actionName === self::SURRENDER_COMMAND) {
            $message->reply('**' . $this->getHero($message)->name . '** is now forever lost in space and time.');
            $this->manager->deleteInteraction($message);
            return;
        }
        if ($actionName === self::CHAR_INFO_COMMAND) {
            $hero = $this->getHero($message);
            $message->reply('', ['embed' => $hero->getStatsAndEffectsEmbed()->setFooter($hero->type->getDefaultFooterText($this->getPrefixedName()))]);
            $this->manager->refreshTimer($message, self::TIMEOUT);
            return;
        }
        if ($actionName === self::CHAR_ACTIONS_COMMAND) {
            $message->reply('', ['embed' => $this->getUserActionsDescriptions($message)]);
            $this->manager->refreshTimer($message, self::TIMEOUT);
            return;
        }
        $embed = $this->processHeroAction($message, $actionName);
        if ($embed === null) {
            $message->reply('Invalid action.');
            return;
        }
        $message->reply('', ['embed' => $embed]);
    }

    public function getUserActionsDescriptions(Message $message): MessageEmbed {
        $res = new MessageEmbed();
        $hero = $this->getHero($message);
        $res->setTitle($hero->name . '\'s abilities and actions:');
        $description = '';
        foreach ($hero->type->actions as $action) {
            $description .= '***' . $action->name . '***' . PHP_EOL . '``' . $action->effect->getDescription() . '``' . PHP_EOL;
        }
        $description .= '*' . $hero->type->defaultAction()->name . '*' . PHP_EOL . '``' . $hero->type->defaultAction()->effect->getDescription() . '``';
        $res->setDescription($description);
        $res->setFooter($hero->type->getDefaultFooterText($this->getPrefixedName()));
        return $res;
    }

    public function processInitialArgs(array $args, bool &$endless, string &$heroClassName) {
        foreach ($args as $str) {
            if ($str === 'endless') {
                $endless = true;
                continue;
            }
            if (mb_strpos($str, 'test-') === 0) {
                $heroClassName = str_replace('_', ' ', mb_strtolower(mb_substr($str, 5)));
                continue;
            }
        }
    }

    /**
     * @param Message $message
     * @param string $actionName
     * @return MessageEmbed|null
     */
    function processHeroAction(Message $message, string $actionName) {
        $hero = $this->getHero($message);
        $action = $hero->type->getActionIfValid($actionName);
        if ($action === null) {
            return null;
        }
        $monster = $this->getMonster($message);
        $target = $action->requiresTarget ? $hero : $monster;
        $embed = $hero->getHeroTurn($action, $target);

        if (!$hero->isDead()) {
            if (!$monster->isDead()) {
                $extraEmbed = $monster->getTurn($hero, $monster->type->getRandomAction());
                $embed->addField($monster->type->name . '\'s turn!', '*``' . $monster->getHealthStatus() . '``*');
                CommandHelper::mergeEmbed($embed, $extraEmbed);
            } else {
                if ($this->getEndless($message)) {
                    $monster = $this->getRandomMonster();
                    $embed->addField('***' . $monster->type->name . ' emerges from the darkness!***', '*``' . $monster->getHealthStatus() . '``*');
                    CommandHelper::mergeEmbed($embed, $monster->getTurn($hero, $monster->type->getRandomAction()));
                    $this->updateMonster($message, $monster);
                } else {
                    $embed->setFooter($hero->name . ' is victorious!', $message->author->getAvatarURL());
                    $this->manager->deleteInteraction($message);
                    return $embed;
                }
            }
        }

        if ($hero->isDead()) {
            $embed->setFooter('R.I.P. ' . $hero->name, $message->author->getAvatarURL());
            $this->manager->deleteInteraction($message);
            return $embed;
        }

        $this->manager->refreshTimer($message, self::TIMEOUT);
        $embed->setFooter($hero->type->getDefaultFooterText($this->getPrefixedName()));
        return $embed;
    }

    function getRandomMonster(): Monster {
        return new Monster($this->monsterTypes[mt_rand(0, $this->numOfTypes)]);
    }

    function getEncounterEmbed(Hero $hero, Monster $monster, bool $heroFirst): MessageEmbed {
        $embed = new MessageEmbed();
        $embed->setTitle('**' . $hero->name . '**');
        $embed->setThumbnail($hero->type->image);
        $embed->setDescription('*``' . $hero->type->description . '``*' . PHP_EOL . '``' . $hero->getStatus() . '``');
        $embed->addField(
            'You encounter a vile **' . $monster->type->name . '**',
            '*``' . $monster->type->description . '``*' . PHP_EOL . '*``' . $monster->getHealthStatus() . '``*'
        );
        $embed->setImage($monster->type->image);
        $embed->setFooter($hero->type->getDefaultFooterText($this->getPrefixedName()));

        if (!$heroFirst) {
            $additionalEmbed = $monster->getTurn($hero, $monster->type->getRandomAction());
            CommandHelper::mergeEmbed($embed, $additionalEmbed);
        }

        return $embed;
    }


    function getHero(Message $message): Hero {
        return $this->manager->getUserData($message)[0];
    }

    function getMonster(Message $message): Monster {
        return $this->manager->getUserData($message)[1];
    }

    function getEndless(Message $message): bool {
        return $this->manager->getUserData($message)[2];
    }

    function updateMonster(Message $message, Monster $monster) {
        $newData = $this->manager->getUserData($message);
        unset($newData[1]);
        $newData[1] = $monster;
        $this->manager->updateData($message, $newData);
    }

}