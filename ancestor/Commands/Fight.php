<?php
/**
 * Created by PhpStorm.
 * User: KolBrony
 * Date: 19.06.2019
 * Time: 14:38
 */

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;
use Ancestor\CommandHandler\TimedCommandManager;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\HeroClass;
use Ancestor\Interaction\Monster;
use Ancestor\Interaction\MonsterType;
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
    private $commandManager;

    /**
     * @var MonsterType[]
     */
    private $monsterTypes;
    /**
     * @var int
     */
    private $numOfTypes;

    public function __construct(CommandHandler $handler, string $name, string $description, array $aliases = null) {
        //TODO all the construction
        parent::__construct($handler, $name, $description, $aliases);
    }

    public function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        if (!empty($args) && !$this->commandManager->userIsInteracting($message->author->id)) {
            return;
        }
        if (empty($args) && !$this->commandManager->userIsInteracting($message->author->id)) {
            $hero = new Hero($this->classes[mt_rand(0, $this->numOfClasses)]);
            $monster = new Monster($this->monsterTypes[mt_rand(0, $this->numOfTypes)]);
            $this->commandManager->addInteraction($message, self::TIMEOUT, [$hero, $monster]);
            $heroFirst = (bool)mt_rand(0, 1);
            $message->reply('', ['embed' => $this->getEncounterEmbed($hero, $monster, $heroFirst)]);
        }
    }

    function getEncounterEmbed(Hero $hero, Monster $monster, bool $heroFirst): MessageEmbed {
        $embed = new MessageEmbed();
        $embed->setTitle('You are a: **' . $hero->type->name . '**');
        $embed->setThumbnail($hero->type->image);
        $embed->setDescription($hero->type->description);
        $embed->addField(
            '``You encounter a vile`` **' . $monster->type->name . '**',
            '*' . $monster->type->description . '*'
        );
        $embed->setImage($monster->type->image);
        if ($heroFirst) {
            $embed->setFooter($hero->type->getDefaultFooterText($this->handler->prefix . $this->name));
        }
        return $embed;
    }
}