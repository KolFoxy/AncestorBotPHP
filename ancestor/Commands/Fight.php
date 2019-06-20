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
use Ancestor\Interaction\PlayerClass;

class Fight extends Command {


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
        if (empty($args) && !$this->commandManager->userIsInteracting($message->author->id)) {
            $hero = new Hero($this->classes[mt_rand(0, $this->numOfClasses)]);
            $monster = new Monster($this->monsterTypes[mt_rand(0, $this->numOfTypes)]);
        }
    }
}