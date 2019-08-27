<?php
return (new class() extends \Ancestor\Interaction\Incident\Incident {
    public function __construct() {
        $this->name = 'Take a closer look at the hammer.';
        $this->description = 'Bla-bla';
        $this->actions = [new \Ancestor\Interaction\Incident\Special\BlindSmith\UseTheHammerAction()];
        $this->image = 'https://i.imgur.com/f6BcThP.png';
    }
});