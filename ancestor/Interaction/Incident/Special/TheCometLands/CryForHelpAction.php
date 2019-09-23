<?php

namespace Ancestor\Interaction\Incident\Special\TheCometLands;

use Ancestor\Interaction\Effect;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\Incident\IActionSingletonInterface;
use Ancestor\Interaction\Incident\Incident;
use Ancestor\Interaction\Incident\IncidentAction;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class CryForHelpAction extends IncidentAction implements IActionSingletonInterface {
    protected static $instance = null;

    public static function getInstance(): IncidentAction {
        if (self::$instance === null) {
            self::$instance = new CryForHelpAction();
        }
        return self::$instance;
    }

    protected function __construct() {
        $this->name = 'Cry for help';
        $this->effect = new Effect();
        $this->effect->setDescription('You try to scream, but the only sound you manage to produce is a pathetic exhale, accompanied by painful cough. It is sure that nobody heard you, nobody could. With each minute passed, you feel weaker and weaker. No matter what you try, how much you want to, how frustrated you are–you can’t move an inch. At this point, the only thing left to do is acce…' . PHP_EOL .
            'The Farmstead lies right before you. And you are going to uncover whatever secrets and treasure are hidden in this timeless chaos.');
    }

    public function getResult(Hero $hero, MessageEmbed $res): ?Incident {
        $res->setTitle('*' . $this->name . '*');
        $heroName = $hero->name;
        $hero->reset();
        $res->setThumbnail($hero->type->image);
        $hero->name = $heroName;
        $res->setDescription('*``' . $this->effect->getDescription() . '``*' . PHP_EOL
            . $heroName . ': **``HP at max``**, **``Stress at 0``**, **``Trinkets are lost``**');
        return null;
    }
}