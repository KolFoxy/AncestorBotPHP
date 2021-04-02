<?php

namespace Ancestor\Interaction\Incident\Special\TheCometLands;

use Ancestor\Interaction\Effect;
use Ancestor\Interaction\Incident\IActionSingletonInterface;
use Ancestor\Interaction\Incident\Incident;
use Ancestor\Interaction\Incident\IncidentAction;
use Ancestor\Interaction\Incident\IncidentCollection\IncidentCollection;

class PickUpStoneAction extends IncidentAction implements IActionSingletonInterface {
    protected static ?IncidentAction $instance = null;

    public static function getInstance(): IncidentAction {
        if (self::$instance === null) {
            self::$instance = new PickUpStoneAction();
        }
        return self::$instance;
    }

    protected function __construct() {
        $this->name = 'Pick up a glowing stone';
        $this->effect = new Effect();
        $this->effect->setDescription('As soon as you touch the artifact, your vision fades to black. You try to move back, but your body doesn’t react. You soon realize that you can’t do anything or feel anything at all. But right before you realize the full futility of your situation, your vision explodes with colors and shapes. They shift and change as if you were in the middle of a giant kaleidoscope. This continues on while you notice a faint high-pitched noise, as if small mosquito was flying just at the edge of your hearing. The noise slowly becomes louder and louder, while the rate color shifting increases. It doesn’t take long for this assault on your senses to become completely unbearable and you wish you could scream or hide or close your eyes. And just when you think that you will become completely deaf and maddened from all this chaos–it all stops.');
    }

    /**
     * @return Incident|null
     */
    public function getResultIncident(): ?Incident {
        return IncidentCollection::getInstance()->randIncident();
    }
}