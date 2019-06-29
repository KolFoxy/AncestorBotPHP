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

class Fight extends Command {

    const TIMEOUT = 300.0;

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
            $this->monsterTypes = array_merge($this->monsterTypes,$arrayOfMonsterTypes);
        }

        $this->numOfClasses = sizeof($this->classes) - 1;
        $this->numOfTypes = sizeof($this->monsterTypes) - 1;

    }

    public function run(Message $message, array $args) {
        if (!$this->manager->userIsInteracting($message)) {
            $endless = false;
            if (!empty($args)) {
                if ($args[0] === 'endless') {
                    $endless = true;
                } else {
                    return;
                }
            }

            $hero = new Hero($this->classes[mt_rand(0, $this->numOfClasses)], $message->author->username);
            $monster = $this->getRandomMonster();
            $heroFirst = (bool)mt_rand(0, 1);
            $message->reply('', ['embed' => $this->getEncounterEmbed($hero, $monster, $heroFirst)]);
            $this->manager->addInteraction($message, self::TIMEOUT, [$hero, $monster, $endless]);
            return;
        }
        if (empty($args)) {
            return;
        }

        $actionName = implode(' ', $args);
        $embed = $this->processAction($message, $actionName);
        if ($embed === null) {
            return;
        }
        $message->reply('', ['embed' => $embed]);
        //TODO: Monster turn via command with no args

    }

    function processAction(Message $message, string $actionName): MessageEmbed {
        $hero = $this->getHero($message);
        $action = $hero->type->getActionIfValid($actionName);
        if ($action === null) {
            return null;
        }
        $monster = $this->getMonster($message);
        $target = $action->requiresTarget ? $hero : $monster;
        $embed = $hero->getHeroTurn($action, $target);
        if (!$monster->isDead()) {
            $extraEmbed = $monster->getMonsterTurn($hero);
            $embed->addField($monster->type->name . '\'s turn!', $monster->getHealthString());
            CommandHelper::mergeEmbed($embed, $extraEmbed);
            $embed->setImage($extraEmbed->thumbnail['url']);
        } else {
            if ($this->getEndless($message)) {
                $monster = $this->getRandomMonster();
                $embed->addField($monster->type->name . ' emerges from the darkness!', $monster->getHealthString());
                CommandHelper::mergeEmbed($embed, $monster->getMonsterTurn($hero));
            } else {
                $embed->setFooter($hero->name . ' is victorious!', $message->author->getAvatarURL());
                $this->manager->deleteInteraction($message);
                return $embed;
            }
        }
        if ($hero->isDead()) {
            $embed->setFooter('R.I.P. ' . $hero->name, $message->author->getAvatarURL());
            $this->manager->deleteInteraction($message);
        }
        $this->manager->refreshTimer($message, self::TIMEOUT);
        $embed->setFooter($hero->type->getDefaultFooterText($this->handler->prefix));
        return $embed;
    }

    function getRandomMonster(): Monster {
        return new Monster($this->monsterTypes[mt_rand(0, $this->numOfTypes)]);
    }

    function getEncounterEmbed(Hero $hero, Monster $monster, bool $heroFirst): MessageEmbed {
        $embed = new MessageEmbed();
        $embed->setTitle($hero->name);
        $embed->setThumbnail($hero->type->image);
        $embed->setDescription($hero->type->description);
        $embed->addField(
            '``You encounter a vile`` **' . $monster->type->name . '**',
            '*' . $monster->type->description . '*' . PHP_EOL . 'Health: ' . $monster->getHealthStatus()
        );
        $embed->setImage($monster->type->image);
        $embed->setFooter($hero->type->getDefaultFooterText($this->handler->prefix . $this->name));

        if (!$heroFirst) {
            $additionalEmbed = $monster->getMonsterTurn($hero);
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

}