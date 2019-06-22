<?php

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;
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
    private $classes;

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
    private $monsterTypes;
    /**
     * @var int
     */
    private $numOfTypes;

    public function __construct(CommandHandler $handler, string $name, string $description, array $aliases = null) {
        //TODO: all the construction
        //TODO: endless mode
        parent::__construct($handler, $name, $description, $aliases);
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
            $monster = new Monster($this->monsterTypes[mt_rand(0, $this->numOfTypes)]);
            $heroFirst = (bool)mt_rand(0, 1);
            $message->reply('', ['embed' => $this->getEncounterEmbed($hero, $monster, $heroFirst)]);
            $this->manager->addInteraction($message, self::TIMEOUT, [$hero, $monster, $endless]);
            return;
        }
        if (empty($args)) {
            return;
        }

        $actionName = implode(' ', $args);
        $hero = $this->getHero($message);
        $action = $hero->type->getActionIfValid($actionName);
        if ($action === null) {
            return;
        }
        $monster = $this->getMonster($message);

        $message->reply('', ['embed' => $hero->getHeroTurn($action, $monster)]);

        //TODO: Monster turn

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
            $embed->addField($additionalEmbed->title, $additionalEmbed->description);
            foreach ($additionalEmbed->fields as $field) {
                $embed->addField($field['name'], $field['value']);
            }
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