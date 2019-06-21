<?php

namespace Ancestor\Interaction;

abstract class AbstractLivingInteraction extends AbstractInteraction {
    /**
     * @var int
     * @required
     */
    public $healthMax;

    /**
     * @var int
     */
    public $dodge = 25;

    /**
     * @var int
     */
    public $accuracy = 100;

    /**
     * @param AbstractLivingInteraction $target
     * @return bool
     */
    public function rollWillHit(AbstractLivingInteraction $target) : bool {
        if (mt_rand(1,$this->accuracy) <= $target->dodge){
            return false;
        }
        return true;
    }

}