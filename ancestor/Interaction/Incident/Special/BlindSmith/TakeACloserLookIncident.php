<?php

namespace Ancestor\Interaction\Incident\Special\BlindSmith;


use Ancestor\Interaction\Incident\Incident;
use Ancestor\Interaction\Incident\IncidentAction;
use Ancestor\Interaction\Incident\IncidentSingletonInterface;

class TakeACloserLookIncident extends Incident implements IncidentSingletonInterface {

    protected static $instance = null;
    public static function getInstance() : Incident {
        if (self::$instance === null) {
            self::$instance = new TakeACloserLookIncident();
        }
        return self::$instance;
    }

    protected function __construct() {
        $this->name = 'The hammer looks unremarkable.';
        $this->description = 'As you are about to leave in disappointment, you suddenly see a flash of memory. You can\'t describe what you just saw in words, but the motive was clear. You look at the hammer again and now you know just what kind of a tool had been made with it.';
        $this->actions = [new UseTheHammerAction()];

        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $path = dirname(__DIR__, 5);
        $json = json_decode(file_get_contents($path . '/data/incidents/blind_smith/offerHammerAction.json'));
        $this->actions[] = $mapper->map($json, new IncidentAction());

        $json = json_decode(file_get_contents($path . '/data/incidents/blind_smith/takeHammerAction.json'));
        $this->actions[] = $mapper->map($json, new IncidentAction());
    }

}