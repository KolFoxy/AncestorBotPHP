<?php

namespace Ancestor\Interaction;

abstract class AbstractLivingInteraction extends AbstractInteraction {

    /**
     * @var int
     */
    public $healthMax = 35;

    /**
     * @var DirectAction[]
     */
    public $actions;

    /**
     * @var array
     */
    public $stats = [];

}