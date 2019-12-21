<?php

namespace Ancestor\Interaction\Incident\Special\BlindSmith;

use Ancestor\Interaction\Effect;
use Ancestor\Interaction\Incident\Incident;
use Ancestor\Interaction\Incident\IncidentAction;
use Ancestor\Interaction\Incident\IncidentSingletonInterface;

class ApproachTheManIncident extends Incident implements IncidentSingletonInterface {
    protected static $instance = null;

    public static function getInstance(): Incident {
        if (self::$instance === null) {
            self::$instance = new ApproachTheManIncident();
            self::$instance->addActions();
        }
        return self::$instance;
    }

    protected function __construct() {
        $this->name = 'You got closer to the man.';
        $this->description = 'The man stands unnervingly still. You notice that his glowing teal eyes lack pupils. You hear faint whispers, but fail to make out words.';

        $action = new IncidentAction();
        $action->name = "Wave in front of him";
        $action->effect = new Effect();
        $action->effect->setDescription('He either doesn\'t notice or doesn\'t care.');
        $action->setResultIncident($this);
        $this->actions = [$action];
    }

    protected function addActions(){
        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        foreach (glob(dirname(__DIR__, 5) . '/data/incidents/blind_smith/near_man_actions/*.json') as $path) {
            $this->actions[] = $mapper->map(json_decode(file_get_contents($path)), new IncidentAction());
        }
    }
}