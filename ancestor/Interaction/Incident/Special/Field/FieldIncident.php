<?php

namespace Ancestor\Interaction\Incident\Special\Field;

use Ancestor\Interaction\Effect;
use Ancestor\Interaction\Incident\Incident;
use Ancestor\Interaction\Incident\IncidentAction;
use Ancestor\Interaction\Incident\IncidentSingletonInterface;

class FieldIncident extends Incident implements IncidentSingletonInterface {
    protected static $instance = null;
    public static function getInstance(): Incident {
        if (self::$instance === null) {
            self::$instance = new FieldIncident();
        }
        return self::$instance;
    }


    protected function __construct() {
        $this->name = 'The sun suddenly blinds you.';
        $this->description = 'You find yourself standing in the middle of an open field. People on the horizon (or rather black dots shifting in the distance and contrasting with the yellowness of the field) are collecting crops. The whole scene feels endless and horizonless.';
        $this->image = "https://i.imgur.com/f6BcThP.png";
        $this->disableDefAction = true;

        $this->actions = [];
        $runAction = new IncidentAction();
        $runAction->name = "Run";
        $runAction->effect = new Effect();
        $runAction->effect->setDescription('You run, but nothing changes.');
        $runAction->setResultIncident($this);
        $this->actions[] = $runAction;

        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $mapper->bExceptionOnUndefinedProperty = true;
        $dir = dirname(__DIR__, 5);

        $path = $dir . '/data/incidents/field/ask_gods.json';
        $askAction = new IncidentAction();
        $mapper->map(json_decode(file_get_contents($path)), $askAction);
        $this->actions[] = $askAction;

        $revealAction = new IncidentAction();
        $path = $dir . '/data/incidents/field/reveal.json';
        $mapper->map(json_decode(file_get_contents($path)), $revealAction);
        $this->actions[] = $revealAction;

        $waitAction = new IncidentAction();
        $this->actions[] = $waitAction;
        $waitAction->name = 'Wait';
        $waitAction->effect = new Effect();
        $waitAction->effect->setDescription('Nothing changes. Sun still shines brightly, human dots on the horizon are still working. You can’t even be sure if clouds are moving or you are just imagining it to stay sane.');
        $waitAction->effect->stress_value = 5;
        $waitIncident = new Incident();
        $waitAction->setResultIncident($waitIncident);

        $waitIncident->description = $this->description;
        $waitIncident->name = $this->name;
        $waitIncident->image = $this->image;
        $waitIncident->disableDefAction = true;
        $runAction2 = new IncidentAction();
        $runAction2->name = "Run";
        $runAction2->effect = new Effect();
        $runAction2->effect->setDescription('You run, but nothing changes.');
        $runAction2->setResultIncident($waitIncident);
        $waitIncident->actions = [$runAction2, $askAction, $revealAction];

        $waitAction2 = new IncidentAction();
        $path = $dir . '/data/incidents/field/second_wait_action.json';
        $mapper->map(json_decode(file_get_contents($path)), $waitAction2);
        $waitIncident->actions[] = $waitAction2;
    }
}