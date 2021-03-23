<?php

namespace Ancestor\Interaction\Incident\Special\CrookedStranger;

use Ancestor\Interaction\Incident\IActionSingletonInterface;
use Ancestor\Interaction\Incident\IncidentAction;

class OfferFirstTrinketSingleton implements IActionSingletonInterface {
    /**
     * @var IncidentAction
     */
    protected static $instance = null;

    public static function getInstance(): IncidentAction {
        if (self::$instance === null) {
            self::$instance = new OfferTrinketAction();
        }
        return self::$instance;
    }
}