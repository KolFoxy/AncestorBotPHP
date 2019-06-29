<?php

namespace Ancestor\Interaction;

abstract class AbstractLivingInteraction extends AbstractInteraction {
    /**
     * @var int
     */
    public $healthMax = 35;

    /**
     * @var int
     */
    public $dodge = 25;

    /**
     * @var int
     */
    public $accuracyMod = 0;

    /**
     * @var DirectAction[]
     */
    public $actions;

    /**
     * @var int
     */
    public $bonusCritChance = 3;

}