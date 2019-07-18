<?php

namespace Ancestor\Interaction\Stats;

interface TimedEffectInterface {

    /**
     * @return bool Processes the turn and returns whether or not the effect is still active without processing the turn. TRUE if done
     */
    public function processTurn() : bool;

    /**
     * @return bool Indicates whether or not the effect is still active without processing the turn. TRUE if done
     */
    public function isDone() : bool;

    /**
     * @return bool Whether or not the effect is positive
     */
    public function isPositive() : bool;

    /**
     * @return string Return the type of the effect.
     */
    public function getType() : string;

    /**
     * @return int
     */
    public function getChance() : int;

    /**
     * @return mixed Copy of the object.
     */
    public function clone();

    /**
     * @return bool Whether or not resists should be checked.
     */
    public function guaranteedApplication() : bool;

    /**
     * @return string
     */
    public function __toString() : string;

    /**
     * @return bool
     */
    public function targetsSelf() : bool;

}