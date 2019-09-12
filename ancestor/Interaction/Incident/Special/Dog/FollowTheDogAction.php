<?php

namespace Ancestor\Interaction\Incident\Special\Dog;

use Ancestor\Interaction\Effect;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\Incident\IActionSingletonInterface;
use Ancestor\Interaction\Incident\IncidentAction;
use Ancestor\Interaction\Stats\StatModifier;
use Ancestor\Interaction\Stats\Stats;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class FollowTheDogAction extends IncidentAction implements IActionSingletonInterface {

    protected static $instance = null;

    /**
     * @var IncidentAction;
     */
    protected $alternativeAction;

    public static function getInstance(): IncidentAction {
        if (self::$instance === null) {
            self::$instance = new FollowTheDogAction();
        }
        return self::$instance;
    }

    protected function __construct() {
        $this->name = "Follow the dog";
        $this->effect = new Effect();
        $this->effect->setDescription('You follow the dog to an abandoned barn. Between stacks of putrid hay and empty dog bowl, you couldn’t find anything worth of value. When you are about to leave, the dog brings you an old spiked club. It is sturdy enough and has a good weight to it. When you finally leave, the dog tries to follow you, but within minutes of walking vanishes, and when you turn around you see that the barn has vanished along with it.');
        $this->effect->health_value = 20;
        $this->effect->healthDeviation = 30;

        $buff = new StatModifier();
        $buff->setStat(Stats::STUN_SKILL_CHANCE);
        $buff->chance = -1;
        $buff->value = 50;
        $buff->duration = 8;
        $this->statModifiers = [$buff];

        $this->alternativeAction = new IncidentAction();
        $this->alternativeAction->name = "Follow the dog";
        $this->alternativeAction->effect = new Effect();
        $this->alternativeAction->effect->setDescription('You follow the dog to an abandoned barn. However, as soon as you cross the portal of the door you realize that the insides of the barn are completely destroyed and all there is left is a huge crater. You try to grab onto anything, but nothing is there to save you from falling down.');
        $this->alternativeAction->effect->health_value = -1;
        $this->alternativeAction->effect->healthDeviation = -9;
    }

    public function getResult(Hero $hero, MessageEmbed $res): MessageEmbed {
        if (mt_rand(1, 100) <= 40) {
            return $this->alternativeAction->getResult($hero, $res);
        }
        return parent::getResult($hero, $res);
    }
}